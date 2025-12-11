<?php

declare(strict_types=1);

namespace App\Enums;

enum AIJobType: string
{
    case DECOMPOSE_CARD = 'DECOMPOSE_CARD';
    case GENERATE_TODAY_PLAN = 'GENERATE_TODAY_PLAN';
    case ASSIST_WITH_TASK = 'ASSIST_WITH_TASK';
    case SUGGEST_CARD_REWRITE = 'SUGGEST_CARD_REWRITE';
    case PARSE_INBOX_ITEM = 'PARSE_INBOX_ITEM';
    case CARD_FORMAT = 'CARD_FORMAT';
    case CARD_DECOMPOSE = 'CARD_DECOMPOSE';
    case CARD_IMPROVE = 'CARD_IMPROVE';

    public function label(): string
    {
        return match ($this) {
            self::DECOMPOSE_CARD => 'Decompose Card',
            self::GENERATE_TODAY_PLAN => 'Generate Today Plan',
            self::ASSIST_WITH_TASK => 'Assist With Task',
            self::SUGGEST_CARD_REWRITE => 'Suggest Card Rewrite',
            self::PARSE_INBOX_ITEM => 'Parse Inbox Item',
            self::CARD_FORMAT => 'Format Card',
            self::CARD_DECOMPOSE => 'Decompose Card (Form)',
            self::CARD_IMPROVE => 'Improve Card',
        };
    }
}
