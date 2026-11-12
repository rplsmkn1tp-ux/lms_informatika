<?php

namespace App\Livewire\Guru;

use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\Element;
use App\Models\LearningObjective;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\LessonProgress;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.guru')]
class LessonManagement extends Component
{
    use WithFileUploads;

    // Navigation & URL Synchronization
    #[Url(as: 'tp')]
    public ?int $selectedTpId = null;

    #[Url(as: 'with_materi')]
    public bool $onlyWithLessons = false;

    // Filters & Tabs
    public string $search = '';
    public string $activeTab = 'semua'; // semua, terbit, terjadwal, draf
    public string $filterElement = '';
    public string $filterClass = '';

    // Modal Form State
    public bool $isModalOpen = false;
    public ?int $lessonId = null;

    // Form Fields
    public ?int $element_id = null;
    public ?int $learning_objective_id = null;
    public string $code = '';
    public string $title = '';
    public string $summary = '';
    public string $content = '';
    public ?int $estimated_minutes = 45;
    public int $sort_order = 1;
    public string $status = 'draft'; // draft, published, scheduled
    public ?string $scheduled_at = null; // Y-m-d\TH:i (WIB)
    public array $selectedClassrooms = [];

    // Bahan Bacaan (PDF)
    public $newPdfFile = null;
    public string $newPdfLabel = '';
    public array $existingPdfs = [];
    public array $pendingPdfs = [];

    // Video (YouTube)
    public string $newVideoUrl = '';
    public string $newVideoTitle = '';
    public ?string $newVideoPreviewId = null;
    public array $existingVideos = [];
    public array $pendingVideos = [];

    // Linked Tasks
    public array $selectedTaskIds = [];
    public array $linkedAssessments = [];

    // Quick ATP Modal
    public bool $isAtpModalOpen = false;
    public ?int $atpElementId = null;
    public string $atpCode = '';
    public string $atpTitle = '';
    public string $atpDescription = '';
    public ?int $atpKktp = 75;

    // Progress Modal
    public bool $isProgressModalOpen = false;
    public ?Lesson $progressLesson = null;
    public array $progressStudentList = [];
    public ?int $progressClassFilter = null;
    public string $progressSearch = '';

    public function mount(): void
    {
        $this->scheduled_at = Carbon::now('Asia/Jakarta')->addDays(1)->setHour(8)->setMinute(0)->format('Y-m-d\TH:i');
    }

    public function selectTp(int $tpId): void
    {
        $this->selectedTpId = $tpId;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;

        if ($tab !== 'semua') {
            // Cek apakah TP yang sedang dipilih memiliki materi sesuai status tab ini
            $hasMatchingLessons = false;
            if ($this->selectedTpId) {
                $hasMatchingLessons = Lesson::where('learning_objective_id', $this->selectedTpId)
                    ->where(function ($q) {
                        $q->where('created_by', Auth::id())
                          ->orWhereNull('created_by')
                          ->orWhere('created_by', 1);
                    })
                    ->when($tab === 'terbit', fn($q) => $q->where('status', 'published'))
                    ->when($tab === 'terjadwal', fn($q) => $q->where('status', 'scheduled'))
                    ->when($tab === 'draf', fn($q) => $q->where('status', 'draft'))
                    ->exists();
            }

            // Jika TP saat ini tidak memiliki materi pada tab ini, otomatis beralih ke TP yang memiliki materi
            if (!$hasMatchingLessons) {
                $matchingQuery = Lesson::where(function ($q) {
                        $q->where('created_by', Auth::id())
                          ->orWhereNull('created_by')
                          ->orWhere('created_by', 1);
                    })
                    ->when($tab === 'terbit', fn($q) => $q->where('status', 'published'))
                    ->when($tab === 'terjadwal', fn($q) => $q->where('status', 'scheduled'))
                    ->when($tab === 'draf', fn($q) => $q->where('status', 'draft'));

                if (!empty($this->filterElement)) {
                    $matchingQuery->whereHas('learningObjective', function ($q) {
                        $q->where('element_id', $this->filterElement);
                    });
                }

                $firstMatchingTpId = $matchingQuery->value('learning_objective_id');

                // Jika di element yang difilter tidak ada materi berstatus ini tapi ada di element lain, reset filterElement
                if (!$firstMatchingTpId && !empty($this->filterElement)) {
                    $firstMatchingTpId = Lesson::where(function ($q) {
                            $q->where('created_by', Auth::id())
                              ->orWhereNull('created_by')
                              ->orWhere('created_by', 1);
                        })
                        ->when($tab === 'terbit', fn($q) => $q->where('status', 'published'))
                        ->when($tab === 'terjadwal', fn($q) => $q->where('status', 'scheduled'))
                        ->when($tab === 'draf', fn($q) => $q->where('status', 'draft'))
                        ->value('learning_objective_id');

                    if ($firstMatchingTpId) {
                        $this->filterElement = null;
                    }
                }

                if ($firstMatchingTpId) {
                    $this->selectedTpId = $firstMatchingTpId;
                }
            }
        }
    }

    public function updatedElementId($val): void
    {
        $this->learning_objective_id = null;
    }

    public function updatedLearningObjectiveId($val): void
    {
        if ($val) {
            $tp = LearningObjective::find($val);
            if ($tp) {
                if (empty(trim($this->summary))) {
                    $this->summary = $tp->title . ($tp->description ? ' — ' . $tp->description : '');
                }
                if (empty(trim($this->code))) {
                    $lastCount = Lesson::where('learning_objective_id', $tp->id)->count();
                    $this->code = $tp->code . '.' . ($lastCount + 1);
                }
            }
        }
    }

    public function updatedNewVideoUrl($val): void
    {
        $this->newVideoPreviewId = self::extractYouTubeId($val);
    }

    public static function extractYouTubeId(?string $url): ?string
    {
        if (!$url) return null;
        $url = trim($url);

        $pattern = '%^(?:https?://)?(?:www\.|m\.)?(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|v/|embed/|shorts/))([a-zA-Z0-9_-]{11})(?:[&?#].*)?$%i';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function render()
    {
        $elementsQuery = Element::query();
        if (!empty($this->filterElement)) {
            $elementsQuery->where('id', $this->filterElement);
        }
        $elements = $elementsQuery->orderBy('code')->get();

        $searchTerm = trim($this->search);
        $groupedData = [];
        $allVisibleTps = [];
        $allLessonsCount = 0;
        $publishedLessonsCount = 0;
        $scheduledLessonsCount = 0;
        $draftLessonsCount = 0;

        foreach ($elements as $el) {
            $tps = LearningObjective::where('element_id', $el->id)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->get();

            $tpList = [];

            foreach ($tps as $tp) {
                // Fetch lessons associated with this TP (created by this teacher or template/admin)
                $lessonsBase = Lesson::where('learning_objective_id', $tp->id)
                    ->where(function ($q) {
                        $q->where('created_by', Auth::id())
                          ->orWhereNull('created_by')
                          ->orWhere('created_by', 1);
                    })
                    ->with(['classrooms', 'attachments', 'assessments', 'allAssessments']);

                $rawLessons = $lessonsBase->get();
                $rawCount = $rawLessons->count();

                $pubCount = $rawLessons->where('status', 'published')->count();
                $schedCount = $rawLessons->where('status', 'scheduled')->count();
                $draftCount = $rawLessons->where('status', 'draft')->count();

                // Compute counts for status badges
                $allLessonsCount += $rawCount;
                $publishedLessonsCount += $pubCount;
                $scheduledLessonsCount += $schedCount;
                $draftLessonsCount += $draftCount;

                // Check search match
                $matchesSearch = false;
                if (!empty($searchTerm)) {
                    $term = strtolower($searchTerm);
                    if (str_contains(strtolower($tp->code), $term) || str_contains(strtolower($tp->title), $term)) {
                        $matchesSearch = true;
                    }
                    foreach ($rawLessons as $rl) {
                        if (str_contains(strtolower($rl->code), $term) || str_contains(strtolower($rl->title), $term) || str_contains(strtolower($rl->summary ?? ''), $term)) {
                            $matchesSearch = true;
                            break;
                        }
                    }
                }

                // Filter by toggle "Hanya tampilkan ATP yang sudah ada materinya"
                if ($this->onlyWithLessons && $rawCount === 0) {
                    continue;
                }

                $tabCount = match($this->activeTab) {
                    'terbit' => $pubCount,
                    'terjadwal' => $schedCount,
                    'draf' => $draftCount,
                    default => $rawCount,
                };

                $tpItem = [
                    'tp' => $tp,
                    'raw_count' => $rawCount,
                    'tab_count' => $tabCount,
                    'published_count' => $pubCount,
                    'scheduled_count' => $schedCount,
                    'draft_count' => $draftCount,
                    'has_match' => $matchesSearch,
                ];

                $tpList[] = $tpItem;
                $allVisibleTps[] = $tp;
            }

            if (!empty($tpList)) {
                $groupedData[] = [
                    'element' => $el,
                    'tps' => $tpList,
                ];
            }
        }

        // Auto-select default TP if not set or invalid
        if (!empty($allVisibleTps)) {
            $validTpIds = array_map(fn($t) => $t->id, $allVisibleTps);

            // Fallback default hanya jika selectedTpId masih null atau tidak valid dalam daftar
            if (!$this->selectedTpId || !in_array($this->selectedTpId, $validTpIds)) {
                $firstMatchingTpId = null;

                if ($this->activeTab !== 'semua') {
                    $firstMatchingTpId = Lesson::whereIn('learning_objective_id', $validTpIds)
                        ->where(function ($q) {
                            $q->where('created_by', Auth::id())
                              ->orWhereNull('created_by')
                              ->orWhere('created_by', 1);
                        })
                        ->when($this->activeTab === 'terbit', fn($q) => $q->where('status', 'published'))
                        ->when($this->activeTab === 'terjadwal', fn($q) => $q->where('status', 'scheduled'))
                        ->when($this->activeTab === 'draf', fn($q) => $q->where('status', 'draft'))
                        ->value('learning_objective_id');
                }

                if (!$firstMatchingTpId) {
                    $firstMatchingTpId = Lesson::whereIn('learning_objective_id', $validTpIds)
                        ->where(function ($q) {
                            $q->where('created_by', Auth::id())
                              ->orWhereNull('created_by')
                              ->orWhere('created_by', 1);
                        })
                        ->value('learning_objective_id');
                }

                $this->selectedTpId = $firstMatchingTpId ?: $allVisibleTps[0]->id;
            }
        } else {
            $this->selectedTpId = null;
        }

        // Load details for selected TP
        $selectedTp = null;
        $selectedLessons = collect();

        if ($this->selectedTpId) {
            $selectedTp = LearningObjective::with('element')->find($this->selectedTpId);

            $lessonsQuery = Lesson::where('learning_objective_id', $this->selectedTpId)
                ->where(function ($q) {
                    $q->where('created_by', Auth::id())
                      ->orWhereNull('created_by')
                      ->orWhere('created_by', 1);
                })
                ->with(['classrooms', 'attachments', 'assessments', 'allAssessments', 'progress']);

            // Tab filter
            if ($this->activeTab === 'terbit') {
                $lessonsQuery->where('status', 'published');
            } elseif ($this->activeTab === 'terjadwal') {
                $lessonsQuery->where('status', 'scheduled');
            } elseif ($this->activeTab === 'draf') {
                $lessonsQuery->where('status', 'draft');
            }

            // Classroom filter
            if (!empty($this->filterClass)) {
                $lessonsQuery->whereHas('classrooms', function ($q) {
                    $q->where('classrooms.id', $this->filterClass);
                });
            }

            // Search filter
            if (!empty($searchTerm)) {
                $s = '%' . $searchTerm . '%';
                $lessonsQuery->where(function ($q) use ($s) {
                    $q->where('title', 'like', $s)
                      ->orWhere('code', 'like', $s)
                      ->orWhere('summary', 'like', $s);
                });
            }

            $selectedLessons = $lessonsQuery->orderBy('sort_order')->orderBy('id')->get();

            foreach ($selectedLessons as $lesson) {
                $lesson->pdf_count = $lesson->attachments->where('type', 'pdf')->count();
                $lesson->video_count = $lesson->attachments->where('type', 'youtube')->count();
                $lesson->tugas_count = $lesson->allAssessments->count();
                $lesson->discussions_count = $lesson->discussions()->whereNull('parent_id')->count();
            }
        }

        $myClassrooms = Auth::user()->taughtClassrooms;
        $availableElements = Element::orderBy('code')->get();

        $availableTps = [];
        if ($this->element_id) {
            $availableTps = LearningObjective::where('element_id', $this->element_id)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->get();
        }

        // Available tasks for linking in modal
        $availableTasksForModal = Assessment::where('type', 'tugas')
            ->where(function ($q) {
                $q->where('created_by', Auth::id())
                  ->orWhere('created_by', 1);
            })
            ->orderBy('title')
            ->get();

        return view('livewire.guru.lesson-management', [
            'groupedData' => $groupedData,
            'selectedTp' => $selectedTp,
            'selectedLessons' => $selectedLessons,
            'myClassrooms' => $myClassrooms,
            'availableElements' => $availableElements,
            'availableTps' => $availableTps,
            'availableTasksForModal' => $availableTasksForModal,
            'statusCounts' => [
                'semua' => $allLessonsCount,
                'terbit' => $publishedLessonsCount,
                'terjadwal' => $scheduledLessonsCount,
                'draf' => $draftLessonsCount,
            ],
        ]);
    }

    public function create(): void
    {
        $this->createForTp($this->selectedTpId);
    }

    public function createForTp(?int $tpId = null): void
    {
        $this->resetValidation();
        $this->reset([
            'lessonId', 'element_id', 'learning_objective_id', 'code', 'title',
            'summary', 'content', 'estimated_minutes', 'sort_order', 'status',
            'selectedClassrooms', 'existingPdfs', 'pendingPdfs', 'existingVideos',
            'pendingVideos', 'selectedTaskIds', 'linkedAssessments', 'newPdfFile',
            'newPdfLabel', 'newVideoUrl', 'newVideoTitle', 'newVideoPreviewId'
        ]);

        $this->status = 'draft';
        $this->estimated_minutes = 45;
        $this->sort_order = 1;

        $targetTpId = $tpId ?: $this->selectedTpId;
        if ($targetTpId) {
            $tp = LearningObjective::find($targetTpId);
            if ($tp) {
                $this->element_id = $tp->element_id;
                $this->learning_objective_id = $tp->id;
                $this->summary = $tp->title . ($tp->description ? ' — ' . $tp->description : '');
                $lastCount = Lesson::where('learning_objective_id', $tp->id)->count();
                $this->code = $tp->code . '.' . ($lastCount + 1);
                $this->sort_order = $lastCount + 1;
            }
        } else {
            $firstEl = Element::orderBy('code')->first();
            if ($firstEl) {
                $this->element_id = $firstEl->id;
                $firstTp = LearningObjective::where('element_id', $firstEl->id)->orderBy('sort_order')->first();
                if ($firstTp) {
                    $this->learning_objective_id = $firstTp->id;
                    $this->summary = $firstTp->title . ($firstTp->description ? ' — ' . $firstTp->description : '');
                    $lastCount = Lesson::where('learning_objective_id', $firstTp->id)->count();
                    $this->code = $firstTp->code . '.' . ($lastCount + 1);
                    $this->sort_order = $lastCount + 1;
                }
            }
        }

        $this->selectedClassrooms = Auth::user()->taughtClassrooms->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $this->selectedTaskIds = [];
        $this->isModalOpen = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();

        $lesson = Lesson::with(['attachments', 'classrooms', 'learningObjective.element', 'allAssessments'])->findOrFail($id);
        if ($lesson->created_by !== Auth::id() && $lesson->created_by !== 1 && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $this->lessonId = $lesson->id;
        $this->element_id = $lesson->learningObjective?->element_id;
        $this->learning_objective_id = $lesson->learning_objective_id;
        $this->code = $lesson->code ?? '';
        $this->title = $lesson->title;
        $this->summary = $lesson->summary ?? '';
        $this->content = $lesson->content ?? '';
        $this->estimated_minutes = $lesson->estimated_minutes ?? 45;
        $this->sort_order = $lesson->sort_order ?? 1;
        $this->status = $lesson->status;
        $this->scheduled_at = $lesson->scheduled_at ? Carbon::parse($lesson->scheduled_at)->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i') : Carbon::now('Asia/Jakarta')->addDays(1)->format('Y-m-d\TH:i');

        $this->selectedClassrooms = $lesson->classrooms->pluck('id')->map(fn($cid) => (string) $cid)->toArray();

        // Load existing PDFs and Videos
        $this->existingPdfs = $lesson->attachments->where('type', 'pdf')->values()->toArray();
        $this->existingVideos = $lesson->attachments->where('type', 'youtube')->values()->toArray();
        $this->pendingPdfs = [];
        $this->pendingVideos = [];
        $this->newPdfFile = null;
        $this->newPdfLabel = '';
        $this->newVideoUrl = '';
        $this->newVideoTitle = '';
        $this->newVideoPreviewId = null;

        // Linked task IDs
        $this->selectedTaskIds = $lesson->allAssessments->pluck('id')->map(fn($v) => (int)$v)->toArray();

        $this->linkedAssessments = [];
        foreach ($lesson->allAssessments as $ass) {
            $this->linkedAssessments[] = [
                'id' => $ass->id,
                'title' => $ass->title,
                'due_at' => $ass->due_at ? Carbon::parse($ass->due_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Tidak ada tenggat',
                'submission_count' => $ass->submissions()->count(),
            ];
        }

        $this->selectedTpId = $lesson->learning_objective_id;
        $this->isModalOpen = true;
    }

    public function addPdf(): void
    {
        $this->validate([
            'newPdfFile' => 'required|file|mimes:pdf|max:20480',
            'newPdfLabel' => 'required|string|max:255',
        ], [
            'newPdfFile.required' => 'Pilih file PDF bahan bacaan terlebih dahulu.',
            'newPdfFile.mimes' => 'File harus berupa dokumen PDF.',
            'newPdfFile.max' => 'Ukuran file PDF maksimal 20 MB.',
            'newPdfLabel.required' => 'Beri judul/keterangan untuk bahan bacaan ini.',
        ]);

        $fileSize = $this->newPdfFile->getSize();
        $storedPath = $this->newPdfFile->store('lesson_attachments', 'local');

        $maxOrder = 0;
        foreach ($this->existingPdfs as $ep) $maxOrder = max($maxOrder, $ep['sort_order'] ?? 0);
        foreach ($this->pendingPdfs as $pp) $maxOrder = max($maxOrder, $pp['sort_order'] ?? 0);

        if ($this->lessonId) {
            $att = LessonAttachment::create([
                'lesson_id' => $this->lessonId,
                'label' => $this->newPdfLabel,
                'file_path' => $storedPath,
                'type' => 'pdf',
                'sort_order' => $maxOrder + 1,
                'file_size' => $fileSize,
            ]);
            $this->existingPdfs[] = $att->toArray();
        } else {
            $this->pendingPdfs[] = [
                'label' => $this->newPdfLabel,
                'file_path' => $storedPath,
                'type' => 'pdf',
                'sort_order' => $maxOrder + 1,
                'file_size' => $fileSize,
            ];
        }

        $this->reset(['newPdfFile', 'newPdfLabel']);
        session()->flash('material_feedback', 'Bahan bacaan PDF berhasil ditambahkan.');
    }

    public function removeExistingPdf(int $attId): void
    {
        $att = LessonAttachment::findOrFail($attId);
        if ($att->lesson->created_by !== Auth::id() && $att->lesson->created_by !== 1 && Auth::user()->role !== 'admin') abort(403);

        if (Storage::disk('local')->exists($att->file_path)) {
            Storage::disk('local')->delete($att->file_path);
        }
        if (Storage::disk('public')->exists($att->file_path)) {
            Storage::disk('public')->delete($att->file_path);
        }

        $att->delete();
        $this->existingPdfs = array_values(array_filter($this->existingPdfs, fn($item) => $item['id'] !== $attId));
        session()->flash('material_feedback', 'Bahan bacaan berhasil dihapus.');
    }

    public function removePendingPdf(int $idx): void
    {
        if (isset($this->pendingPdfs[$idx])) {
            $path = $this->pendingPdfs[$idx]['file_path'] ?? null;
            if ($path && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
            unset($this->pendingPdfs[$idx]);
            $this->pendingPdfs = array_values($this->pendingPdfs);
        }
    }

    public function addVideo(): void
    {
        $this->validate([
            'newVideoUrl' => 'required|string',
            'newVideoTitle' => 'required|string|max:255',
        ], [
            'newVideoUrl.required' => 'Masukkan tautan video YouTube.',
            'newVideoTitle.required' => 'Beri judul video pembelajaran ini.',
        ]);

        $videoId = self::extractYouTubeId($this->newVideoUrl);
        if (!$videoId) {
            $this->addError('newVideoUrl', 'Tautan bukan video YouTube yang valid. Gunakan format youtube.com atau youtu.be.');
            return;
        }

        $maxOrder = 0;
        foreach ($this->existingVideos as $ev) $maxOrder = max($maxOrder, $ev['sort_order'] ?? 0);
        foreach ($this->pendingVideos as $pv) $maxOrder = max($maxOrder, $pv['sort_order'] ?? 0);

        if ($this->lessonId) {
            $att = LessonAttachment::create([
                'lesson_id' => $this->lessonId,
                'label' => $this->newVideoTitle,
                'file_path' => $videoId,
                'type' => 'youtube',
                'sort_order' => $maxOrder + 1,
            ]);
            $this->existingVideos[] = $att->toArray();
        } else {
            $this->pendingVideos[] = [
                'label' => $this->newVideoTitle,
                'file_path' => $videoId,
                'type' => 'youtube',
                'sort_order' => $maxOrder + 1,
            ];
        }

        $this->reset(['newVideoUrl', 'newVideoTitle', 'newVideoPreviewId']);
        session()->flash('material_feedback', 'Video YouTube berhasil ditambahkan.');
    }

    public function removeExistingVideo(int $attId): void
    {
        $att = LessonAttachment::findOrFail($attId);
        if ($att->lesson->created_by !== Auth::id() && $att->lesson->created_by !== 1 && Auth::user()->role !== 'admin') abort(403);

        $att->delete();
        $this->existingVideos = array_values(array_filter($this->existingVideos, fn($item) => $item['id'] !== $attId));
        session()->flash('material_feedback', 'Video berhasil dihapus.');
    }

    public function removePendingVideo(int $idx): void
    {
        if (isset($this->pendingVideos[$idx])) {
            unset($this->pendingVideos[$idx]);
            $this->pendingVideos = array_values($this->pendingVideos);
        }
    }

    public function movePdf(int $index, string $direction, bool $isExisting = false): void
    {
        if ($isExisting) {
            $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;
            if ($targetIndex < 0 || $targetIndex >= count($this->existingPdfs)) return;

            $current = $this->existingPdfs[$index];
            $other = $this->existingPdfs[$targetIndex];

            $currentAtt = LessonAttachment::find($current['id']);
            $otherAtt = LessonAttachment::find($other['id']);

            if ($currentAtt && $otherAtt) {
                $tempOrder = $currentAtt->sort_order ?: ($index + 1);
                $currentAtt->sort_order = $otherAtt->sort_order ?: ($targetIndex + 1);
                $otherAtt->sort_order = $tempOrder;
                $currentAtt->save();
                $otherAtt->save();

                if ($this->lessonId) {
                    $this->existingPdfs = LessonAttachment::where('lesson_id', $this->lessonId)->where('type', 'pdf')->orderBy('sort_order')->orderBy('id')->get()->toArray();
                }
            }
        } else {
            $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;
            if ($targetIndex < 0 || $targetIndex >= count($this->pendingPdfs)) return;

            $temp = $this->pendingPdfs[$index];
            $this->pendingPdfs[$index] = $this->pendingPdfs[$targetIndex];
            $this->pendingPdfs[$targetIndex] = $temp;
        }
    }

    public function moveVideo(int $index, string $direction, bool $isExisting = false): void
    {
        if ($isExisting) {
            $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;
            if ($targetIndex < 0 || $targetIndex >= count($this->existingVideos)) return;

            $current = $this->existingVideos[$index];
            $other = $this->existingVideos[$targetIndex];

            $currentAtt = LessonAttachment::find($current['id']);
            $otherAtt = LessonAttachment::find($other['id']);

            if ($currentAtt && $otherAtt) {
                $tempOrder = $currentAtt->sort_order ?: ($index + 1);
                $currentAtt->sort_order = $otherAtt->sort_order ?: ($targetIndex + 1);
                $otherAtt->sort_order = $tempOrder;
                $currentAtt->save();
                $otherAtt->save();

                if ($this->lessonId) {
                    $this->existingVideos = LessonAttachment::where('lesson_id', $this->lessonId)->where('type', 'youtube')->orderBy('sort_order')->orderBy('id')->get()->toArray();
                }
            }
        } else {
            $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;
            if ($targetIndex < 0 || $targetIndex >= count($this->pendingVideos)) return;

            $temp = $this->pendingVideos[$index];
            $this->pendingVideos[$index] = $this->pendingVideos[$targetIndex];
            $this->pendingVideos[$targetIndex] = $temp;
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'element_id' => 'required|exists:elements,id',
            'learning_objective_id' => 'required|exists:learning_objectives,id',
            'code' => 'required|string|max:50',
            'summary' => 'required|string',
            'content' => 'nullable|string',
            'estimated_minutes' => 'nullable|integer|min:1',
            'sort_order' => 'required|integer',
            'status' => 'required|in:draft,published,scheduled',
            'scheduled_at' => 'required_if:status,scheduled|nullable|date',
            'selectedClassrooms' => 'required|array|min:1',
        ], [
            'title.required' => 'Judul materi wajib diisi.',
            'element_id.required' => 'Pilih Elemen Pembelajaran.',
            'learning_objective_id.required' => 'Pilih Tujuan Pembelajaran (ATP).',
            'code.required' => 'Kode materi wajib diisi.',
            'summary.required' => 'Tujuan pembelajaran materi wajib diisi.',
            'status.required' => 'Pilih status materi.',
            'scheduled_at.required_if' => 'Tentukan tanggal tayang untuk status Terjadwal.',
            'selectedClassrooms.required' => 'Pilih minimal satu kelas tujuan materi ini.',
            'selectedClassrooms.min' => 'Pilih minimal satu kelas tujuan materi ini.',
        ]);

        $scheduledCarbon = null;
        if ($this->status === 'scheduled' && $this->scheduled_at) {
            $scheduledCarbon = Carbon::parse($this->scheduled_at, 'Asia/Jakarta')->setTimezone('UTC');
        }

        DB::transaction(function () use ($scheduledCarbon) {
            $data = [
                'learning_objective_id' => $this->learning_objective_id,
                'code' => $this->code,
                'title' => $this->title,
                'summary' => $this->summary,
                'content' => $this->content,
                'estimated_minutes' => $this->estimated_minutes,
                'sort_order' => $this->sort_order,
                'status' => $this->status,
                'scheduled_at' => $scheduledCarbon,
            ];

            if ($this->lessonId) {
                $lesson = Lesson::findOrFail($this->lessonId);
                if ($lesson->created_by !== Auth::id() && $lesson->created_by !== 1 && Auth::user()->role !== 'admin') abort(403);
                $lesson->update($data);
            } else {
                $data['created_by'] = Auth::id();
                $lesson = Lesson::create($data);
                $this->lessonId = $lesson->id;
            }

            // Sync classrooms
            $syncData = [];
            foreach ($this->selectedClassrooms as $classId) {
                $syncData[$classId] = [
                    'available_from' => $this->status === 'scheduled' ? $scheduledCarbon : null,
                ];
            }
            $lesson->classrooms()->sync($syncData);

            // Commit pending PDFs
            foreach ($this->pendingPdfs as $pdf) {
                $lesson->attachments()->create($pdf);
            }
            $this->pendingPdfs = [];

            // Commit pending Videos
            foreach ($this->pendingVideos as $vid) {
                $lesson->attachments()->create($vid);
            }
            $this->pendingVideos = [];

            // Sync connected tasks (Assessments)
            // Unlink previously linked assessments that are no longer selected
            Assessment::where('lesson_id', $lesson->id)
                ->whereNotIn('id', $this->selectedTaskIds)
                ->update(['lesson_id' => null]);

            // Link newly selected assessments
            if (!empty($this->selectedTaskIds)) {
                Assessment::whereIn('id', $this->selectedTaskIds)
                    ->update([
                        'lesson_id' => $lesson->id,
                        'learning_objective_id' => $lesson->learning_objective_id,
                    ]);
            }

            $this->selectedTpId = $lesson->learning_objective_id;
        });

        session()->flash('message', 'Materi "' . $this->title . '" berhasil disimpan.');
        $this->closeModal();
    }

    public function duplicate(int $id): void
    {
        $lesson = Lesson::with(['attachments', 'classrooms'])->findOrFail($id);
        if ($lesson->created_by !== Auth::id() && $lesson->created_by !== 1 && Auth::user()->role !== 'admin') abort(403);

        DB::transaction(function () use ($lesson) {
            $newLesson = $lesson->replicate(['created_at', 'updated_at']);

            // Unique code generation: -copy, -copy-2, -copy-3
            $baseCode = preg_replace('/-copy(-\d+)?$/i', '', $lesson->code);
            $candidateCode = $baseCode . '-copy';
            $counter = 1;
            while (Lesson::where('code', $candidateCode)->exists()) {
                $counter++;
                $candidateCode = $baseCode . '-copy-' . $counter;
            }
            $newLesson->code = $candidateCode;

            // Title: ensure (Salinan)
            $newLesson->title = str_ends_with(trim($lesson->title), '(Salinan)')
                ? $lesson->title
                : $lesson->title . ' (Salinan)';

            $newLesson->status = 'draft';
            $newLesson->scheduled_at = null;
            $newLesson->created_by = Auth::id();
            $newLesson->save();

            // Replicate classrooms
            $newLesson->classrooms()->sync($lesson->classrooms->pluck('id')->toArray());

            // Replicate attachments with independent files
            foreach ($lesson->attachments as $att) {
                $filePath = $att->file_path;
                if ($att->type === 'pdf' && !empty($att->file_path)) {
                    $ext = pathinfo($att->file_path, PATHINFO_EXTENSION) ?: 'pdf';
                    $newPath = 'lesson_attachments/' . uniqid() . '.' . $ext;

                    if (Storage::disk('local')->exists($att->file_path)) {
                        Storage::disk('local')->copy($att->file_path, $newPath);
                        $filePath = $newPath;
                    } elseif (Storage::disk('public')->exists($att->file_path)) {
                        Storage::disk('public')->copy($att->file_path, $newPath);
                        $filePath = $newPath;
                    }
                }

                $newLesson->attachments()->create([
                    'label' => $att->label,
                    'file_path' => $filePath,
                    'type' => $att->type,
                    'sort_order' => $att->sort_order,
                    'file_size' => $att->file_size,
                ]);
            }

            $this->selectedTpId = $lesson->learning_objective_id;
        });

        session()->flash('message', 'Materi berhasil diduplikat sebagai Draf.');
    }

    public function delete(int $id): void
    {
        $lesson = Lesson::with('attachments')->findOrFail($id);
        if ($lesson->created_by !== Auth::id() && $lesson->created_by !== 1 && Auth::user()->role !== 'admin') abort(403);

        $tpId = $lesson->learning_objective_id;

        // Unlink any assessments
        Assessment::where('lesson_id', $id)->update(['lesson_id' => null]);

        // Delete attachment files from storage
        foreach ($lesson->attachments as $att) {
            if ($att->type === 'pdf' && !empty($att->file_path)) {
                if (Storage::disk('local')->exists($att->file_path)) {
                    Storage::disk('local')->delete($att->file_path);
                }
                if (Storage::disk('public')->exists($att->file_path)) {
                    Storage::disk('public')->delete($att->file_path);
                }
            }
        }

        $lesson->delete();
        $this->selectedTpId = $tpId;
        session()->flash('message', 'Materi berhasil dihapus.');
    }

    public function closeModal(): void
    {
        $this->isModalOpen = false;
    }

    // --- QUICK ATP MODAL ---

    public function openQuickAtpModal(): void
    {
        $this->atpElementId = $this->element_id ?: Element::orderBy('code')->value('id');
        $this->atpCode = '';
        $this->atpTitle = '';
        $this->atpDescription = '';
        $this->atpKktp = 75;
        $this->isAtpModalOpen = true;
    }

    public function closeQuickAtpModal(): void
    {
        $this->isAtpModalOpen = false;
    }

    public function saveQuickAtp(): void
    {
        $this->validate([
            'atpElementId' => 'required|exists:elements,id',
            'atpCode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('learning_objectives', 'code')->where('element_id', $this->atpElementId),
            ],
            'atpTitle' => 'required|string|max:255',
            'atpDescription' => 'nullable|string',
            'atpKktp' => 'nullable|integer|min:0|max:100',
        ], [
            'atpElementId.required' => 'Pilih Elemen.',
            'atpCode.required' => 'Kode ATP wajib diisi (contoh: BK-6).',
            'atpCode.unique' => 'Kode ATP sudah ada pada elemen ini. Silakan gunakan kode unik.',
            'atpTitle.required' => 'Nama/Judul ATP wajib diisi.',
        ]);

        $maxSort = LearningObjective::where('element_id', $this->atpElementId)->max('sort_order') ?? 0;

        $newTp = LearningObjective::create([
            'element_id' => $this->atpElementId,
            'code' => $this->atpCode,
            'title' => $this->atpTitle,
            'description' => $this->atpDescription,
            'sort_order' => $maxSort + 1,
            'kktp' => $this->atpKktp,
            'is_template' => false,
            'created_by' => Auth::id(),
        ]);

        $this->element_id = $newTp->element_id;
        $this->learning_objective_id = $newTp->id;
        $this->selectedTpId = $newTp->id;

        if (empty(trim($this->summary))) {
            $this->summary = $newTp->title . ($newTp->description ? ' — ' . $newTp->description : '');
        }

        $this->isAtpModalOpen = false;
        session()->flash('material_feedback', 'ATP ' . $newTp->code . ' berhasil ditambahkan dan langsung terpilih.');
    }

    // --- REKAP PROGRES SISWA MODAL ---

    public function openProgressModal(int $lessonId): void
    {
        $this->progressLesson = Lesson::with(['classrooms.students', 'attachments', 'assessments'])->findOrFail($lessonId);
        if ($this->progressLesson->created_by !== Auth::id() && $this->progressLesson->created_by !== 1 && Auth::user()->role !== 'admin') abort(403);

        $this->progressClassFilter = null;
        $this->progressSearch = '';

        $readings = $this->progressLesson->attachments->where('type', 'pdf');
        $videos = $this->progressLesson->attachments->where('type', 'youtube');
        $tasks = $this->progressLesson->assessments;

        $totalItems = $readings->count() + $videos->count();

        $list = [];
        foreach ($this->progressLesson->classrooms as $cls) {
            foreach ($cls->students as $student) {
                $progress = LessonProgress::where('lesson_id', $lessonId)
                    ->where('student_id', $student->id)
                    ->first();

                $completedItems = $progress ? ($progress->completed_items ?? []) : [];
                $completedCount = count($completedItems);

                // Cek status tugas terkait
                $tasksDone = 0;
                if ($tasks->isNotEmpty()) {
                    $tasksDone = $tasks->filter(function ($t) use ($student) {
                        return $t->submissions()->where('student_id', $student->id)->exists();
                    })->count();
                }

                $list[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'student_nisn' => $student->nisn ?? $student->username,
                    'classroom_id' => $cls->id,
                    'classroom_name' => $cls->name,
                    'completed_items_count' => $completedCount,
                    'total_items' => $totalItems,
                    'progress_percent' => $totalItems > 0 ? min(100, round(($completedCount / $totalItems) * 100)) : ($progress && $progress->completed_at ? 100 : 0),
                    'is_lesson_completed' => ($progress && $progress->completed_at !== null) || ($totalItems > 0 && $completedCount >= $totalItems),
                    'tasks_submitted' => $tasksDone,
                    'total_tasks' => $tasks->count(),
                    'last_activity' => $progress ? Carbon::parse($progress->updated_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : null,
                ];
            }
        }

        $this->progressStudentList = $list;
        $this->isProgressModalOpen = true;
    }

    public function getFilteredProgressStudentsProperty(): array
    {
        if (empty($this->progressStudentList)) {
            return [];
        }

        return array_values(array_filter($this->progressStudentList, function ($item) {
            if ($this->progressClassFilter !== null && $this->progressClassFilter !== '') {
                if ((int)($item['classroom_id'] ?? 0) !== (int)$this->progressClassFilter) {
                    return false;
                }
            }

            if (!empty(trim($this->progressSearch))) {
                $term = strtolower(trim($this->progressSearch));
                $nameMatch = str_contains(strtolower($item['student_name'] ?? ''), $term);
                $nisnMatch = str_contains(strtolower($item['student_nisn'] ?? ''), $term);
                if (!$nameMatch && !$nisnMatch) {
                    return false;
                }
            }

            return true;
        }));
    }

    public function closeProgressModal(): void
    {
        $this->isProgressModalOpen = false;
        $this->progressLesson = null;
        $this->progressStudentList = [];
        $this->progressClassFilter = null;
        $this->progressSearch = '';
    }
}
