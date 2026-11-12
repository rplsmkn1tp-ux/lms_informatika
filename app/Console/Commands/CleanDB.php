<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\ScheduleEntry;
use App\Models\TeachingAssignment;

class CleanDB extends Command {
    protected $signature = 'clean:db';
    public function handle() {
        $entries = ScheduleEntry::all();
        foreach($entries as $e) {
            $ta = TeachingAssignment::find($e->teaching_assignment_id);
            $className = $ta ? $ta->classroom->name : 'Unknown';
            $this->info("ID: {$e->id}, Day: {$e->day_of_week}, Class: {$className}");
            if ($e->day_of_week == 2 && $className == '10 Akuntansi 1') {
                $this->info("Deleting this entry...");
                $e->delete();
            }
        }
    }
}
