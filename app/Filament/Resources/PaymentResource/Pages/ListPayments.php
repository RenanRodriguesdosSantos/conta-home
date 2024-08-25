<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_payments')
                ->label('Zerar pagamentos pendentes')
                ->form([
                    Select::make('user1')
                        ->live()
                        ->label('Usuário 1')
                        ->required()
                        ->options(User::all()->pluck('name', 'id')),
                    Select::make('user2')
                        ->live()
                        ->label('Usuário 2')
                        ->required()
                        ->options(User::all()->pluck('name', 'id')),
                    Textarea::make('result')
                        ->label(false)
                        ->disabled(function ($get, $set) {
                            $user1 = $get('user1');
                            $user2 = $get('user2');

                            if ($user1 && $user2) {
                                $totalUser1 = Payment::where('payments.user_id', $user1)
                                    ->whereHas('expense', fn (Builder $query) => $query->where('expenses.user_id', $user2))
                                    ->status(PaymentStatus::PENDING)
                                    ->sum('value');

                                $totalUser2 = Payment::where('payments.user_id', $user2)
                                    ->whereHas('expense', fn (Builder $query) => $query->where('expenses.user_id', $user1))
                                    ->status(PaymentStatus::PENDING)
                                    ->sum('value');

                                $user1 = User::find($user1);
                                $user2 = User::find($user2);

                                if ($totalUser1 > $totalUser2) {
                                    $difference = $totalUser1 - $totalUser2;
                                    $message = "O usuário {$user1->name} deve pagar ao usuário {$user2->name} o valor de: R$ " . number_format($difference, 2, ',', '.');
                                } else if ($totalUser1 < $totalUser2) {
                                    $difference = $totalUser2 - $totalUser1;
                                    $message = "O usuário {$user2->name} deve pagar ao usuário {$user1->name} o valor de: R$ " . number_format($difference, 2, ',', '.');
                                } else {
                                    $message = "Os usários não devem pagar nenhum valor";
                                }

                                $set('result', $message);

                                return true;
                            }

                            $set('result', '');
                            return true;
                        })
                ])
                ->modalWidth(MaxWidth::ExtraLarge)
                ->action(function (array $data){
                    /** @var User */
                    $user = auth()->user();

                    $user1 = $data['user1'];
                    $user2 = $data['user2'];

                    $totalUser1 = Payment::where('payments.user_id', $user1)
                        ->whereHas('expense', fn (Builder $query) => $query->where('expenses.user_id', $user2))
                        ->status(PaymentStatus::PENDING)
                        ->sum('value');

                    $totalUser2 = Payment::where('payments.user_id', $user2)
                        ->whereHas('expense', fn (Builder $query) => $query->where('expenses.user_id', $user1))
                        ->status(PaymentStatus::PENDING)
                        ->sum('value');

                    if ($totalUser1 > $totalUser2) {
                        $canReset = $user2;
                    } else if ($totalUser1 < $totalUser2) {
                        $canReset = $user1;
                    } else {
                        $canReset = 'both';
                    }

                    if (!(($canReset == $user->id) || ($canReset == 'both' && $user->id == $user1 || $user->id == $user2))) {
                        Notification::make('cannot_reset')
                            ->title('Somente o usuário que receberá o valor, pode realizar essa ação!')
                            ->danger()
                            ->send();

                        return;
                    }

                    Payment::where('payments.user_id', $user1)
                        ->whereHas('expense', fn (Builder $query) => $query->where('expenses.user_id', $user2))
                        ->status(PaymentStatus::PENDING)
                        ->each(fn ($payment) => $payment->update([
                            'status' => PaymentStatus::PAID,
                            'paid_at' => now()
                        ]));

                    Payment::where('payments.user_id', $user2)
                        ->whereHas('expense', fn (Builder $query) => $query->where('expenses.user_id', $user1))
                        ->status(PaymentStatus::PENDING)
                        ->each(fn ($payment) => $payment->update([
                            'status' => PaymentStatus::PAID,
                            'paid_at' => now()
                        ]));

                    Notification::make('reset_payments_message')
                        ->title('Ação realizada com sucesso!')
                        ->success()
                        ->send();
                })
        ];
    }
}
