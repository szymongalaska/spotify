<?php

declare(strict_types=1);

namespace App\Service;

class TrackService
{
    /**
     * Remove not available tracks from array of tracks
     * @param array $tracks Array of tracks
     * @return array Filtered array
     */
    public function filterNotAvailableTracks(array $tracks)
    {
        return array_filter($tracks, function ($track) {

            if ($track['track']['is_local'] === true)
                return true;

            return $track['track']['is_playable'];
        });
    }

    /**
     * Remove available tracks from array of tracks
     * @param array $tracks Array of tracks
     * @return array Filtered array
     */
    public function filterAvailableTracks(array $tracks)
    {
        return array_filter($tracks, function ($track) {

            if ($track['track']['is_local'] === true)
                return false;

            return !$track['track']['is_playable'];
        });
    }
}