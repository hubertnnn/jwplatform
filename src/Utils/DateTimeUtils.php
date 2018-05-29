<?php

namespace HubertNNN\JwPlatform\Utils;

use Carbon\Carbon;

class DateTimeUtils
{
    public static function timestampToDate($timestamp)
    {
        if($timestamp === null)
            return null;

        if(class_exists(Carbon::class)) {
            return Carbon::createFromTimestamp($timestamp);
        } else {
            $date = new \DateTime();
            $date->setTimestamp($timestamp);
            return $date;
        }
    }

    public static function dateToTimestamp($date)
    {
        if($date === null)
            return null;

        /** @var \DateTime $date */
        return $date->getTimestamp();
    }

}
