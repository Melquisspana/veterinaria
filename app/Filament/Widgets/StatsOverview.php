<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Vaccination;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $currency = config('vet.currency');
        $days = config('vet.vaccine_reminder_days');

        $todayPending = Appointment::scheduled()->onDate(today())->count();
        $todayTotal = Appointment::onDate(today())->where('status', '!=', 'cancelled')->count();
        $income = (float) Payment::where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
        $pending = (float) Payment::where('status', PaymentStatus::Pending)->sum('amount');

        return [
            Stat::make('Visitas de hoy', $todayPending)
                ->description($todayTotal > $todayPending ? ($todayTotal - $todayPending).' ya realizadas' : 'pendientes')
                ->icon('heroicon-o-truck')
                ->url(AppointmentResource::getUrl('index', ['tab' => 'today'])),
            Stat::make('Vacunas por vencer', Vaccination::dueWithin($days)->count())
                ->description("próximos {$days} días")
                ->icon('heroicon-o-shield-check')
                ->color('warning'),
            Stat::make('Por cobrar', Number::currency($pending, $currency))
                ->icon('heroicon-o-exclamation-circle')
                ->color($pending > 0 ? 'danger' : 'success')
                ->url(PaymentResource::getUrl('index', ['tab' => 'pending'])),
            Stat::make('Ingresos del mes', Number::currency($income, $currency))
                ->description(now()->translatedFormat('F Y'))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
