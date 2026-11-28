<?php
function internshipRequirementParseDate($value)
{
    if (!is_string($value) || $value === '') {
        return null;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value ? $date : null;
}

function internshipRequirementParseMonth($value)
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    if (preg_match('/^(\d{4})[-\/](\d{1,2})(?:[-\/]\d{1,2})?$/', $value, $matches)) {
        return ['year' => (int)$matches[1], 'month' => (int)$matches[2]];
    }
    if (preg_match('/^(\d{1,2})[-\/](?:\d{1,2}[-\/])?(\d{4})$/', $value, $matches)) {
        return ['year' => (int)$matches[2], 'month' => (int)$matches[1]];
    }
    if (preg_match('/^(\d{1,2})$/', $value, $matches)) {
        return ['year' => null, 'month' => (int)$matches[1]];
    }

    $monthNames = [
        'jan' => 1, 'january' => 1,
        'feb' => 2, 'february' => 2,
        'mar' => 3, 'march' => 3,
        'apr' => 4, 'april' => 4,
        'may' => 5,
        'jun' => 6, 'june' => 6,
        'jul' => 7, 'july' => 7,
        'aug' => 8, 'august' => 8,
        'sep' => 9, 'sept' => 9, 'september' => 9,
        'oct' => 10, 'october' => 10,
        'nov' => 11, 'november' => 11,
        'dec' => 12, 'december' => 12,
    ];
    if (!preg_match('/\b(january|jan|february|feb|march|mar|april|apr|may|june|jun|july|jul|august|aug|september|sept|sep|october|oct|november|nov|december|dec)\b/i', $value, $monthMatch)) {
        return null;
    }

    preg_match('/\b((?:19|20)\d{2})\b/', $value, $yearMatch);
    return [
        'year' => isset($yearMatch[1]) ? (int)$yearMatch[1] : null,
        'month' => $monthNames[strtolower($monthMatch[1])],
    ];
}

function internshipRequirementSubmittedMonths(array $periods, array $submittedLabels)
{
    $completed = [];
    foreach ($submittedLabels as $label) {
        $submittedMonth = internshipRequirementParseMonth($label);
        if ($submittedMonth === null || $submittedMonth['month'] < 1 || $submittedMonth['month'] > 12) {
            continue;
        }

        foreach ($periods as $period) {
            if ($period['month'] !== $submittedMonth['month'] ||
                ($submittedMonth['year'] !== null && $period['year'] !== $submittedMonth['year']) ||
                isset($completed[$period['key']])) {
                continue;
            }

            $completed[$period['key']] = true;
            break;
        }
    }

    return count($completed);
}

function internshipRequirementProgress($startValue, $endValue, array $attendanceLabels, array $accomplishmentLabels, $finalSubmitted, DateTimeImmutable $today)
{
    $startDate = internshipRequirementParseDate($startValue);
    $endDate = internshipRequirementParseDate($endValue);
    $internshipEnded = $endDate !== null && $endDate <= $today;
    $monthlyDueThrough = $internshipEnded
        ? $endDate
        : $today->modify('first day of this month')->modify('-1 day');
    if (!$internshipEnded && $today->format('Y-m-d') === $today->modify('last day of this month')->format('Y-m-d')) {
        $monthlyDueThrough = $today;
    }

    $periods = [];
    if ($startDate !== null && $startDate <= $today && $monthlyDueThrough >= $startDate) {
        $startMonth = $startDate->modify('first day of this month');
        $endMonth = $monthlyDueThrough->modify('first day of this month');
        for ($month = $startMonth; $month <= $endMonth; $month = $month->modify('+1 month')) {
            $periods[] = [
                'key' => $month->format('Y-m'),
                'year' => (int)$month->format('Y'),
                'month' => (int)$month->format('n'),
            ];
        }
    }

    $monthlyDue = count($periods);
    $attendanceSubmitted = internshipRequirementSubmittedMonths($periods, $attendanceLabels);
    $accomplishmentSubmitted = internshipRequirementSubmittedMonths($periods, $accomplishmentLabels);
    $finalDue = $internshipEnded ? 1 : 0;
    $finalComplete = $finalDue && (bool)$finalSubmitted ? 1 : 0;

    return [
        'attendance_due' => $monthlyDue,
        'attendance_submitted' => $attendanceSubmitted,
        'accomplishment_due' => $monthlyDue,
        'accomplishment_submitted' => $accomplishmentSubmitted,
        'final_due' => $finalDue,
        'final_submitted' => $finalComplete,
        'requirements_due' => ($monthlyDue * 2) + $finalDue,
        'requirements_submitted' => $attendanceSubmitted + $accomplishmentSubmitted + $finalComplete,
        'internship_ended' => $internshipEnded,
    ];
}
