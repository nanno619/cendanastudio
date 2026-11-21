<?php

namespace App\Support;

use App\Enums\ClientStatus;
use App\Enums\LookupCategory;
use App\Models\Client;
use App\Models\LookupItem;

/**
 * Dropdown options for the CRM client form. States offer active items plus the client's stored one.
 */
class ClientForm
{
    /**
     * @return array{states: array<string, string>, statuses: array<string, string>}
     */
    public static function options(?Client $client = null): array
    {
        $states = LookupItem::options(LookupCategory::MalaysianStates);

        if (filled($client?->state) && ! isset($states[$client->state])) {
            $states[$client->state] = LookupItem::labels(LookupCategory::MalaysianStates)[$client->state] ?? $client->state;
        }

        return [
            'states' => $states,
            'statuses' => collect(ClientStatus::cases())->mapWithKeys(fn (ClientStatus $status): array => [$status->value => __($status->label())])->all(),
        ];
    }
}
