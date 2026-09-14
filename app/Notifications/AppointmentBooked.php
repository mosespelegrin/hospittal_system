<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentBooked extends Notification
{
    /**
     * @param  bool  $forStaff  Whether this copy is the internal "new
     *                          booking" alert rather than the patient's
     *                          own confirmation.
     */
    public function __construct(
        protected Appointment $appointment,
        protected bool $forStaff = false,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment->loadMissing(['patient', 'doctor.department']);

        $when = $appointment->appointment_date->format('d M Y')
            . ' at ' . $appointment->appointment_time->format('H:i');

        if ($this->forStaff) {
            return (new MailMessage)
                ->subject('New consultation request — ' . $appointment->patient->full_name)
                ->greeting('New booking received')
                ->line($appointment->patient->full_name . ' (' . $appointment->patient->patient_number . ') requested a consultation.')
                ->line('Doctor: ' . ($appointment->doctor->name ?? 'Unassigned'))
                ->line('Department: ' . ($appointment->doctor->department->name ?? 'N/A'))
                ->line('Requested for: ' . $when)
                ->line('Chief complaint: ' . ($appointment->notes ?? '—'));
        }

        return (new MailMessage)
            ->subject('Your consultation request — Amani Hospital')
            ->greeting('Hi ' . $appointment->patient->full_name . ',')
            ->line("We've received your consultation request.")
            ->line('Patient number: ' . $appointment->patient->patient_number)
            ->line('Doctor: ' . ($appointment->doctor->name ?? 'To be assigned'))
            ->line('Requested for: ' . $when)
            ->line('We will contact you to confirm your appointment.');
    }
}
