<?php

namespace App\Support;

class PlayerTitleResolver
{
    public function resolve(int $eloTotal): array
    {
        $titlesConfig = config('player_titles', []);

        if (!is_array($titlesConfig) || $titlesConfig === []) {
            return [
                'slug' => 'unranked',
                'label' => 'Unranked',
                'min_elo' => 0,
            ];
        }

        $titles = array_values(array_filter($titlesConfig, function ($title): bool {
            return is_array($title)
                && isset($title['min_elo'], $title['slug'], $title['label'])
                && is_numeric($title['min_elo']);
        }));

        if (empty($titles)) {
            return [
                'slug' => 'unranked',
                'label' => 'Unranked',
                'min_elo' => 0,
            ];
        }

        $titles = array_map(function (array $title): array {
            return [
                'slug' => (string) $title['slug'],
                'label' => (string) $title['label'],
                'min_elo' => (int) $title['min_elo'],
            ];
        }, $titles);

        usort($titles, function (array $left, array $right): int {
            return $left['min_elo'] <=> $right['min_elo'];
        });

        $current = [
            'slug' => 'unranked',
            'label' => 'Unranked',
            'min_elo' => 0,
        ];

        foreach ($titles as $title) {
            if ($eloTotal >= $title['min_elo']) {
                $current = $title;
            }
        }

        return $current;
    }
}
