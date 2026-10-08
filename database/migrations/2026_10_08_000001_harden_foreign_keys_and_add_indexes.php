<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deleting a department or doctor must never silently hard-delete the
        // doctors / appointments (patient medical history) beneath it.
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->foreign('doctor_id')->references('id')->on('doctors')->restrictOnDelete();

            // Slot-conflict lookups and per-day listings.
            $table->index(['doctor_id', 'appointment_date', 'appointment_time'], 'appointments_doctor_slot_index');
            $table->index(['appointment_date', 'status'], 'appointments_date_status_index');
        });

        Schema::table('patients', function (Blueprint $table) {
            // Existing-patient lookup on booking.
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex(['phone']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_date_status_index');
            $table->dropIndex('appointments_doctor_slot_index');
            $table->dropForeign(['doctor_id']);
            $table->foreign('doctor_id')->references('id')->on('doctors')->cascadeOnDelete();
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
        });
    }
};
