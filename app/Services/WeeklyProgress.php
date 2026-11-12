<?php

namespace App\Services;

use App\Models\LessonProgress;
use App\Models\Submission;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Target mingguan & hari beruntun (SPEC bagian 8).
 * Minggu = Senin–Minggu zona Asia/Jakarta. Hari beruntun dihitung dari hari yang punya
 * aktivitas belajar (subbab selesai atau asesmen dikumpulkan), tanpa tabel tambahan.
 */
class WeeklyProgress
{
    public const TIMEZONE = 'Asia/Jakarta';

    public function __construct(private User $student) {}

    public function completedThisWeek(?CarbonImmutable $now = null): int
    {
        $now = ($now ?? CarbonImmutable::now())->setTimezone(self::TIMEZONE);
        $start = $now->startOfWeek(CarbonImmutable::MONDAY)->setTimezone(config('app.timezone'));
        $end = $now->endOfWeek(CarbonImmutable::SUNDAY)->setTimezone(config('app.timezone'));

        return LessonProgress::query()
            ->where('student_id', $this->student->id)
            ->whereBetween('completed_at', [$start, $end])
            ->count();
    }

    public function target(): int
    {
        return (int) ($this->student->activeClassroom()?->weekly_lesson_target ?? 5);
    }

    public function streak(?CarbonImmutable $now = null): int
    {
        $now = ($now ?? CarbonImmutable::now())->setTimezone(self::TIMEZONE);
        $days = $this->activityDays($now->subDays(366));

        $cursor = $now->startOfDay();
        // Belum ada aktivitas hari ini: beruntun tetap dihitung sampai kemarin.
        if (! $days->contains($cursor->toDateString())) {
            $cursor = $cursor->subDay();
        }

        $streak = 0;
        while ($days->contains($cursor->toDateString())) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    /** @return Collection<int, string> tanggal (Y-m-d, Asia/Jakarta) yang punya aktivitas */
    private function activityDays(CarbonImmutable $since): Collection
    {
        $lessonDates = LessonProgress::query()
            ->where('student_id', $this->student->id)
            ->where('completed_at', '>=', $since->setTimezone(config('app.timezone')))
            ->pluck('completed_at');

        $submissionDates = Submission::query()
            ->where('student_id', $this->student->id)
            ->where('submitted_at', '>=', $since->setTimezone(config('app.timezone')))
            ->pluck('submitted_at');

        return $lessonDates->merge($submissionDates)
            ->filter()
            ->map(fn ($date) => CarbonImmutable::parse($date, config('app.timezone'))->setTimezone(self::TIMEZONE)->toDateString())
            ->unique()
            ->values();
    }
}
