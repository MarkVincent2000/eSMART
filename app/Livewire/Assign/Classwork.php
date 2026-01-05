<?php

namespace App\Livewire\Assign;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Grading\Classroom;
use App\Models\Grading\Assignment;
use App\Models\Grading\AssignmentAttachment;
use App\Models\Grading\ClassroomStudent;
use App\Models\Comment;
use App\Models\CommentAttachment;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Classwork extends Component
{
    use WithFileUploads, WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public $classroomId;
    public $classroom;
    public $search = '';
    public $isStudent = false;

    // Assignment creation form
    public $showAssignmentForm = false;
    public $editingAssignment = null;
    
    // Delete modal
    public $showDeleteModal = false;
    public $deleteAssignmentId = null;
    public $deleteAssignmentTitle = null;
    
    // Students modal
    public $showStudentsModal = false;
    
    // Submissions view
    public $showSubmissionsForAssignmentId = null;
    
    // Comments view
    public $showCommentsForAssignmentId = null;
    public $commentBody = '';
    public $commentAttachments = [];
    public $editingCommentId = null;
    public $editingCommentBody = '';
    public $editingCommentAttachments = [];
    public $editingCommentExistingAttachments = [];
    
    // Delete comment modal
    public $showDeleteCommentModal = false;
    public $deleteCommentId = null;
    public $deleteCommentBody = null;
    
    // View submission modal
    public $showViewSubmissionModal = false;
    public $selectedSubmission = null;
    public $gradingPointsEarned = null;
    public $gradingFeedback = '';
    
    // Submission modal
    public $showSubmissionModal = false;
    public $submissionAssignmentId = null;
    public $submissionContent = '';
    public $submissionAttachments = [];
    public $existingSubmission = null;
    public $studentInfo = null;
    
    // Attachment viewer
    public $showAttachmentModal = false;
    public $selectedAttachment = null;
    public $selectedAttachmentUrl = null;
    public $title = '';
    public $description = '';
    public $instructions = '';
    public $points_possible = null;
    public $due_date = null;
    public $due_time = null;
    public $assignment_type = 'assignment';
    public $component_category = null;
    public $attachments = [];
    public $isLoading = false;

    public function mount($classroomId = null)
    {
        $this->classroomId = $classroomId ?? request()->get('id');
        
        // Check if user is a student
        $user = Auth::user();
        if ($user) {
            $this->studentInfo = \App\Models\StudentDetails\StudentInfo::where('user_id', $user->id)->first();
            $this->isStudent = $this->studentInfo !== null;
        }
        
        if ($this->classroomId) {
            $this->loadClassroom();
        }
    }

    public function loadClassroom()
    {
        if (!$this->classroomId) {
            $this->classroom = null;
            return;
        }

        $query = Classroom::with([
            'section',
            'semester',
            'subject',
            'creator',
            'students.user',
            'students.program',
            'students.section'
        ])
        ->where('id', $this->classroomId);

        // Check if user is a student
        if ($this->isStudent && $this->studentInfo) {
            // Students can view if they are enrolled
            $query->whereHas('students', function($q) {
                $q->where('student_infos.id', $this->studentInfo->id)
                  ->where('classroom_student.status', 'enrolled');
            });
        } else {
            // Teachers can view if they created it
            $query->where('created_by', Auth::id());
        }

        $this->classroom = $query->first();
    }

    public function toggleAssignmentForm()
    {
        $this->showAssignmentForm = !$this->showAssignmentForm;
        if (!$this->showAssignmentForm) {
            $this->resetAssignmentForm();
        }
    }

    public function resetAssignmentForm()
    {
        $this->editingAssignment = null;
        $this->title = '';
        $this->description = '';
        $this->instructions = '';
        $this->points_possible = null;
        $this->due_date = null;
        $this->due_time = null;
        $this->assignment_type = 'assignment';
        $this->component_category = null;
        $this->attachments = [];
        $this->resetErrorBag();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function viewAttachment($attachmentId)
    {
        $attachment = AssignmentAttachment::find($attachmentId);
        
        if ($attachment) {
            $this->selectedAttachment = $attachment;
            $this->selectedAttachmentUrl = $attachment->url;
            $this->showAttachmentModal = true;
        }
    }

    public function viewSubmissionAttachment($attachmentId)
    {
        $attachment = \App\Models\Grading\AssignmentSubmissionAttachment::find($attachmentId);
        
        if ($attachment) {
            $this->selectedAttachment = $attachment;
            $this->selectedAttachmentUrl = $attachment->url;
            $this->showAttachmentModal = true;
        }
    }

    public function closeAttachmentModal()
    {
        $this->showAttachmentModal = false;
        $this->selectedAttachment = null;
        $this->selectedAttachmentUrl = null;
    }

    public function removeAttachment($index)
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function editAssignment($assignmentId)
    {
        $assignment = Assignment::with('attachments')->find($assignmentId);
        
        if (!$assignment) {
            $this->dispatch('show-toast', [
                'message' => 'Assignment not found.',
                'type' => 'error'
            ]);
            return;
        }

        // Check if user owns this assignment
        if ($assignment->created_by !== Auth::id()) {
            $this->dispatch('show-toast', [
                'message' => 'You do not have permission to edit this assignment.',
                'type' => 'error'
            ]);
            return;
        }

        $this->editingAssignment = $assignment;
        $this->title = $assignment->title;
        $this->description = $assignment->description ?? '';
        $this->instructions = $assignment->instructions ?? '';
        $this->points_possible = $assignment->points_possible;
        
        // Format dates properly
        if ($assignment->due_date) {
            $this->due_date = $assignment->due_date instanceof \Carbon\Carbon 
                ? $assignment->due_date->format('Y-m-d') 
                : \Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d');
        } else {
            $this->due_date = null;
        }
        
        if ($assignment->due_time) {
            $dueTime = $assignment->due_time instanceof \Carbon\Carbon 
                ? $assignment->due_time 
                : \Carbon\Carbon::parse($assignment->due_time);
            $this->due_time = $dueTime->format('H:i');
        } else {
            $this->due_time = null;
        }
        
        $this->assignment_type = $assignment->assignment_type;
        $this->component_category = $assignment->component_category;
        $this->attachments = [];
        $this->showAssignmentForm = true;
    }

    public function deleteAssignment($assignmentId)
    {
        $assignment = Assignment::find($assignmentId);
        
        if (!$assignment) {
            $this->dispatch('show-toast', [
                'message' => 'Assignment not found.',
                'type' => 'error'
            ]);
            return;
        }

        // Check if user owns this assignment
        if ($assignment->created_by !== Auth::id()) {
            $this->dispatch('show-toast', [
                'message' => 'You do not have permission to delete this assignment.',
                'type' => 'error'
            ]);
            return;
        }

        $this->deleteAssignmentId = $assignment->id;
        $this->deleteAssignmentTitle = $assignment->title;
        $this->showDeleteModal = true;
    }

    public function confirmDelete()
    {
        if ($this->deleteAssignmentId) {
            try {
                DB::beginTransaction();
                
                $assignment = Assignment::findOrFail($this->deleteAssignmentId);
                
                // Check if user owns this assignment
                if ($assignment->created_by !== Auth::id()) {
                    throw new \Exception('You do not have permission to delete this assignment.');
                }
                
                $assignmentTitle = $assignment->title;
                
                // Delete attachments from storage
                foreach ($assignment->attachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment->file_path)) {
                        Storage::disk('public')->delete($attachment->file_path);
                    }
                    $attachment->delete();
                }
                
                // Delete the assignment (cascade will handle related records)
                $assignment->delete();
                
                DB::commit();
                
                $this->closeDeleteModal();
                
                $this->dispatch('show-toast', [
                    'message' => 'Assignment "' . $assignmentTitle . '" deleted successfully!',
                    'type' => 'success'
                ]);
                
                $this->resetPage();
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->dispatch('show-toast', [
                    'message' => 'An error occurred while deleting the assignment: ' . $e->getMessage(),
                    'type' => 'error'
                ]);
            }
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteAssignmentId = null;
        $this->deleteAssignmentTitle = null;
    }

    public function openStudentsModal()
    {
        $this->showStudentsModal = true;
    }

    public function closeStudentsModal()
    {
        $this->showStudentsModal = false;
    }

    public function toggleSubmissions($assignmentId)
    {
        if ($this->showSubmissionsForAssignmentId === $assignmentId) {
            $this->showSubmissionsForAssignmentId = null;
        } else {
            $this->showSubmissionsForAssignmentId = $assignmentId;
        }
    }

    public function toggleComments($assignmentId)
    {
        if ($this->showCommentsForAssignmentId === $assignmentId) {
            $this->showCommentsForAssignmentId = null;
            $this->commentBody = '';
            $this->commentAttachments = [];
        } else {
            $this->showCommentsForAssignmentId = $assignmentId;
        }
    }

    public function removeCommentAttachment($index)
    {
        unset($this->commentAttachments[$index]);
        $this->commentAttachments = array_values($this->commentAttachments);
    }

    public function submitComment($assignmentId)
    {
        $this->validate([
            'commentBody' => 'required|string|max:5000',
            'commentAttachments.*' => 'nullable|file|max:5120', // 5MB max per file
        ], [
            'commentBody.required' => 'Comment cannot be empty.',
            'commentBody.max' => 'Comment cannot exceed 5000 characters.',
            'commentAttachments.*.max' => 'Each file must not exceed 5MB.',
        ]);

        try {
            DB::beginTransaction();

            $assignment = Assignment::findOrFail($assignmentId);

            // Create comment
            $comment = Comment::create([
                'commentable_type' => Assignment::class,
                'commentable_id' => $assignment->id,
                'user_id' => Auth::id(),
                'body' => $this->commentBody,
                'status' => Comment::STATUS_PUBLISHED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Handle file uploads
            if (!empty($this->commentAttachments)) {
                $displayOrder = 0;
                foreach ($this->commentAttachments as $file) {
                    $path = $file->store('comment-attachments', 'public');
                    
                    CommentAttachment::create([
                        'comment_id' => $comment->id,
                        'file_name' => basename($path),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'display_order' => $displayOrder++,
                    ]);
                }
            }

            DB::commit();

            $this->dispatch('show-toast', [
                'message' => 'Comment posted successfully!',
                'type' => 'success'
            ]);

            // Reset form
            $this->commentBody = '';
            $this->commentAttachments = [];
            $this->resetPage();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', [
                'message' => 'An error occurred while posting the comment: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function editComment($commentId)
    {
        $comment = Comment::with('attachments')->find($commentId);
        
        if (!$comment) {
            $this->dispatch('show-toast', [
                'message' => 'Comment not found.',
                'type' => 'error'
            ]);
            return;
        }

        // Check if user owns this comment
        if ($comment->user_id !== Auth::id()) {
            $this->dispatch('show-toast', [
                'message' => 'You can only edit your own comments.',
                'type' => 'error'
            ]);
            return;
        }

        $this->editingCommentId = $comment->id;
        $this->editingCommentBody = $comment->body;
        $this->editingCommentAttachments = [];
        $this->editingCommentExistingAttachments = $comment->attachments;
    }

    public function cancelEditComment()
    {
        $this->editingCommentId = null;
        $this->editingCommentBody = '';
        $this->editingCommentAttachments = [];
        $this->editingCommentExistingAttachments = [];
    }

    public function removeEditingCommentAttachment($index)
    {
        unset($this->editingCommentAttachments[$index]);
        $this->editingCommentAttachments = array_values($this->editingCommentAttachments);
    }

    public function updateComment($commentId)
    {
        $this->validate([
            'editingCommentBody' => 'required|string|max:5000',
            'editingCommentAttachments.*' => 'nullable|file|max:5120', // 5MB max per file
        ], [
            'editingCommentBody.required' => 'Comment cannot be empty.',
            'editingCommentBody.max' => 'Comment cannot exceed 5000 characters.',
            'editingCommentAttachments.*.max' => 'Each file must not exceed 5MB.',
        ]);

        try {
            DB::beginTransaction();

            $comment = Comment::findOrFail($commentId);

            // Check if user owns this comment
            if ($comment->user_id !== Auth::id()) {
                throw new \Exception('You can only edit your own comments.');
            }

            // Update comment body
            $comment->update([
                'body' => $this->editingCommentBody,
            ]);

            // Handle new file uploads
            if (!empty($this->editingCommentAttachments)) {
                $existingCount = $comment->attachments()->count();
                $displayOrder = $existingCount;
                
                foreach ($this->editingCommentAttachments as $file) {
                    $path = $file->store('comment-attachments', 'public');
                    
                    CommentAttachment::create([
                        'comment_id' => $comment->id,
                        'file_name' => basename($path),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'display_order' => $displayOrder++,
                    ]);
                }
            }

            DB::commit();

            $this->dispatch('show-toast', [
                'message' => 'Comment updated successfully!',
                'type' => 'success'
            ]);

            // Reset editing state
            $this->cancelEditComment();
            $this->resetPage();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', [
                'message' => 'An error occurred while updating the comment: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function deleteComment($commentId)
    {
        $comment = Comment::find($commentId);
        
        if (!$comment) {
            $this->dispatch('show-toast', [
                'message' => 'Comment not found.',
                'type' => 'error'
            ]);
            return;
        }

        // Check if user owns this comment
        if ($comment->user_id !== Auth::id()) {
            $this->dispatch('show-toast', [
                'message' => 'You can only delete your own comments.',
                'type' => 'error'
            ]);
            return;
        }

        $this->deleteCommentId = $comment->id;
        $this->deleteCommentBody = Str::limit($comment->body, 100);
        $this->showDeleteCommentModal = true;
    }

    public function confirmDeleteComment()
    {
        if ($this->deleteCommentId) {
            try {
                DB::beginTransaction();
                
                $comment = Comment::findOrFail($this->deleteCommentId);
                
                // Check if user owns this comment
                if ($comment->user_id !== Auth::id()) {
                    throw new \Exception('You can only delete your own comments.');
                }
                
                // Delete attachments from storage
                foreach ($comment->attachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment->file_path)) {
                        Storage::disk('public')->delete($attachment->file_path);
                    }
                    $attachment->delete();
                }
                
                // Delete the comment
                $comment->delete();
                
                DB::commit();
                
                $this->closeDeleteCommentModal();
                
                $this->dispatch('show-toast', [
                    'message' => 'Comment deleted successfully!',
                    'type' => 'success'
                ]);
                
                $this->resetPage();
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->dispatch('show-toast', [
                    'message' => 'An error occurred while deleting the comment: ' . $e->getMessage(),
                    'type' => 'error'
                ]);
            }
        }
    }

    public function closeDeleteCommentModal()
    {
        $this->showDeleteCommentModal = false;
        $this->deleteCommentId = null;
        $this->deleteCommentBody = null;
    }

    public function viewSubmission($submissionId)
    {
        $submission = \App\Models\Grading\AssignmentSubmission::with([
            'studentInfo.user',
            'attachments',
            'assignment',
            'grader'
        ])->find($submissionId);
        
        if (!$submission) {
            $this->dispatch('show-toast', [
                'message' => 'Submission not found.',
                'type' => 'error'
            ]);
            return;
        }

        // If user is a student, only allow viewing their own submission
        if ($this->isStudent && $this->studentInfo) {
            if ($submission->student_info_id !== $this->studentInfo->id) {
                $this->dispatch('show-toast', [
                    'message' => 'You can only view your own submissions.',
                    'type' => 'error'
                ]);
                return;
            }
        }
        
        $this->selectedSubmission = $submission;
        // Initialize grading fields
        $this->gradingPointsEarned = $submission->points_earned;
        $this->gradingFeedback = $submission->feedback ?? '';
        $this->showViewSubmissionModal = true;
    }

    public function closeViewSubmissionModal()
    {
        $this->showViewSubmissionModal = false;
        $this->selectedSubmission = null;
        $this->gradingPointsEarned = null;
        $this->gradingFeedback = '';
    }

    public function saveGrade()
    {
        if (!$this->selectedSubmission || $this->isStudent) {
            $this->dispatch('show-toast', [
                'message' => 'Only teachers can grade submissions.',
                'type' => 'error'
            ]);
            return;
        }

        // Check if user is the classroom creator
        if (!$this->classroom || $this->classroom->created_by !== Auth::id()) {
            $this->dispatch('show-toast', [
                'message' => 'You do not have permission to grade this submission.',
                'type' => 'error'
            ]);
            return;
        }

        $maxPoints = $this->selectedSubmission->assignment->points_possible ?? 100;

        $this->validate([
            'gradingPointsEarned' => 'required|numeric|min:0|max:' . $maxPoints,
            'gradingFeedback' => 'nullable|string|max:5000',
        ], [
            'gradingPointsEarned.required' => 'Points earned is required.',
            'gradingPointsEarned.numeric' => 'Points earned must be a number.',
            'gradingPointsEarned.min' => 'Points earned cannot be negative.',
            'gradingPointsEarned.max' => 'Points earned cannot exceed ' . $maxPoints . ' points.',
        ]);

        try {
            DB::beginTransaction();

            $this->selectedSubmission->gradeSubmission(
                (float) $this->gradingPointsEarned,
                Auth::id(),
                $this->gradingFeedback ?: null
            );

            // Reload the submission to get updated data
            $this->selectedSubmission->refresh();
            $this->selectedSubmission->load(['grader', 'assignment', 'studentInfo.user']);

            DB::commit();

            // Create notifications for both student and teacher when grade is saved
            $this->createGradeNotifications($this->selectedSubmission);

            $this->dispatch('show-toast', [
                'message' => 'Grade saved successfully!',
                'type' => 'success'
            ]);

            // Reset page to refresh the submissions list
            $this->resetPage();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', [
                'message' => 'An error occurred while saving the grade: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function openSubmissionModal($assignmentId)
    {
        if (!$this->isStudent || !$this->studentInfo) {
            $this->dispatch('show-toast', [
                'message' => 'Only students can submit assignments.',
                'type' => 'error'
            ]);
            return;
        }

        $assignment = Assignment::find($assignmentId);
        if (!$assignment) {
            $this->dispatch('show-toast', [
                'message' => 'Assignment not found.',
                'type' => 'error'
            ]);
            return;
        }

        // Check if student is assigned to this assignment OR enrolled in the classroom
        $isAssignedToAssignment = $assignment->students()->where('student_infos.id', $this->studentInfo->id)->exists();
        $isEnrolledInClassroom = $this->classroom && $this->classroom->students()->where('student_infos.id', $this->studentInfo->id)->exists();
        
        if (!$isAssignedToAssignment && !$isEnrolledInClassroom) {
            $this->dispatch('show-toast', [
                'message' => 'You are not enrolled in this class.',
                'type' => 'error'
            ]);
            return;
        }
        
        // Auto-assign student to assignment if enrolled in classroom but not assigned
        if (!$isAssignedToAssignment && $isEnrolledInClassroom) {
            $assignment->students()->syncWithoutDetaching([$this->studentInfo->id]);
        }

        $this->submissionAssignmentId = $assignmentId;
        
        // Check if submission already exists
        $this->existingSubmission = \App\Models\Grading\AssignmentSubmission::where('assignment_id', $assignmentId)
            ->where('student_info_id', $this->studentInfo->id)
            ->with(['attachments', 'grader'])
            ->first();

        if ($this->existingSubmission) {
            $this->submissionContent = $this->existingSubmission->content ?? '';
        } else {
            $this->submissionContent = '';
        }

        $this->submissionAttachments = [];
        $this->showSubmissionModal = true;
    }

    public function closeSubmissionModal()
    {
        $this->showSubmissionModal = false;
        $this->submissionAssignmentId = null;
        $this->submissionContent = '';
        $this->submissionAttachments = [];
        $this->existingSubmission = null;
        $this->resetErrorBag();
    }

    public function removeSubmissionAttachment($index)
    {
        unset($this->submissionAttachments[$index]);
        $this->submissionAttachments = array_values($this->submissionAttachments);
    }

    public function submitAssignment()
    {
        if (!$this->isStudent || !$this->studentInfo || !$this->submissionAssignmentId) {
            return;
        }

        // Check if submission is already graded
        if ($this->existingSubmission && $this->existingSubmission->status === 'graded') {
            $this->dispatch('show-toast', [
                'message' => 'This submission has been graded and cannot be updated. Please contact your teacher if you need to make changes.',
                'type' => 'error'
            ]);
            return;
        }

        $this->validate([
            'submissionContent' => 'nullable|string',
            'submissionAttachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ], [
            'submissionAttachments.*.max' => 'Each file must not exceed 10MB.',
        ]);

        try {
            DB::beginTransaction();

            $assignment = Assignment::findOrFail($this->submissionAssignmentId);

            // Check if student is assigned
            $isAssigned = $assignment->students()->where('student_infos.id', $this->studentInfo->id)->exists();
            if (!$isAssigned) {
                throw new \Exception('You are not assigned to this assignment.');
            }

            // Double-check that submission is not graded (in case it was graded between opening modal and submitting)
            if ($this->existingSubmission && $this->existingSubmission->status === 'graded') {
                throw new \Exception('This submission has been graded and cannot be updated.');
            }

            // Check if assignment is past due
            $isLate = $assignment->isPastDue();
            $status = $isLate ? \App\Models\Grading\AssignmentSubmission::STATUS_LATE : \App\Models\Grading\AssignmentSubmission::STATUS_SUBMITTED;

            $isNewSubmission = false;
            // Create or update submission
            if ($this->existingSubmission) {
                // Update existing submission
                $submission = $this->existingSubmission;
                $submission->update([
                    'content' => $this->submissionContent,
                    'status' => $status,
                    'submitted_at' => now(),
                    'is_late' => $isLate,
                ]);
            } else {
                // Create new submission
                $isNewSubmission = true;
                $submission = \App\Models\Grading\AssignmentSubmission::create([
                    'assignment_id' => $this->submissionAssignmentId,
                    'student_info_id' => $this->studentInfo->id,
                    'submitted_by' => Auth::id(),
                    'content' => $this->submissionContent,
                    'status' => $status,
                    'submitted_at' => now(),
                    'is_late' => $isLate,
                ]);
            }

            // Handle file uploads
            if (!empty($this->submissionAttachments)) {
                $existingAttachments = $submission->attachments()->count();
                $displayOrder = $existingAttachments;
                
                foreach ($this->submissionAttachments as $file) {
                    $path = $file->store('assignment-submissions', 'public');
                    
                    \App\Models\Grading\AssignmentSubmissionAttachment::create([
                        'submission_id' => $submission->id,
                        'file_name' => basename($path),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'display_order' => $displayOrder++,
                    ]);
                }
            }

            DB::commit();

            // Create notification for teacher when student submits
            if ($isNewSubmission && $assignment->created_by) {
                $this->createSubmissionNotification($submission, $assignment);
            }

            $this->dispatch('show-toast', [
                'message' => $isLate ? 'Assignment submitted successfully (marked as late).' : 'Assignment submitted successfully!',
                'type' => 'success'
            ]);

            $this->closeSubmissionModal();
            $this->resetPage();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', [
                'message' => 'An error occurred while submitting: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function createAssignment()
    {
        if (!$this->classroom) {
            return;
        }

        $isEditing = !is_null($this->editingAssignment);

        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'points_possible' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date' . ($isEditing ? '' : '|after_or_equal:today'),
            'due_time' => 'nullable|date_format:H:i',
            'assignment_type' => 'required|in:assignment,project,homework,activity,laboratory,research,presentation,other',
            'component_category' => 'nullable|in:written_works,performance_tasks',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ], [
            'title.required' => 'Assignment title is required.',
            'due_date.after_or_equal' => 'Due date must be today or later.',
        ]);

        $this->isLoading = true;

        try {
            DB::beginTransaction();

            // Combine due_date and due_time if both are provided
            $dueDateTime = null;
            if ($this->due_date) {
                if ($this->due_time) {
                    $dueDateTime = \Carbon\Carbon::parse($this->due_date . ' ' . $this->due_time);
                } else {
                    $dueDateTime = \Carbon\Carbon::parse($this->due_date);
                }
            }

            $assignmentData = [
                'title' => $this->title,
                'description' => $this->description,
                'instructions' => $this->instructions,
                'assignment_type' => $this->assignment_type,
                'component_category' => $this->component_category,
                'points_possible' => $this->points_possible,
                'due_date' => $this->due_date,
                'due_time' => $dueDateTime,
            ];

            if ($isEditing) {
                // Update existing assignment
                $assignment = $this->editingAssignment;
                $assignment->update($assignmentData);
                $message = 'Assignment updated successfully!';
            } else {
                // Create new assignment
                $assignmentData['classroom_id'] = $this->classroom->id;
                $assignmentData['subject_id'] = $this->classroom->subject_id;
                $assignmentData['subject_type'] = $this->classroom->subject_type;
                $assignmentData['section_id'] = $this->classroom->section_id;
                $assignmentData['semester_id'] = $this->classroom->semester_id;
                $assignmentData['status'] = Assignment::STATUS_PUBLISHED;
                $assignmentData['created_by'] = Auth::id();
                $assignmentData['published_at'] = now();
                
                $assignment = Assignment::create($assignmentData);

                // Auto-assign to all students from classroom_student pivot table
                $classroomStudents = ClassroomStudent::where('classroom_id', $this->classroom->id)
                    ->pluck('student_info_id')
                    ->toArray();
                
                if (!empty($classroomStudents)) {
                    $assignment->students()->sync($classroomStudents);
                    
                    // Create notifications for students when new assignment is created
                    $this->createAssignmentNotifications($assignment, $classroomStudents);
                }
                
                $message = 'Assignment created successfully!';
            }

            // Handle new file uploads
            if (!empty($this->attachments)) {
                $existingAttachments = $assignment->attachments()->count();
                $displayOrder = $existingAttachments;
                
                foreach ($this->attachments as $file) {
                    $path = $file->store('assignment-attachments', 'public');
                    
                    AssignmentAttachment::create([
                        'assignment_id' => $assignment->id,
                        'file_name' => basename($path),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'display_order' => $displayOrder++,
                    ]);
                }
            }

            DB::commit();

            $this->dispatch('show-toast', [
                'message' => $message,
                'type' => 'success'
            ]);

            $this->resetAssignmentForm();
            $this->showAssignmentForm = false;
            $this->resetPage();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', [
                'message' => 'An error occurred while saving the assignment: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        $assignments = collect([]);
        
        if ($this->classroom) {
            $query = Assignment::with([
                'creator',
                'attachments',
                'students' => function($q) {
                    $q->select('student_infos.id', 'student_infos.student_number', 'student_infos.user_id');
                },
                'students.user',
                'students.program',
                'students.section',
                'comments.user',
                'comments.attachments'
            ])
            ->withCount(['attachments', 'students', 'submissions', 'comments'])
            ->where('classroom_id', $this->classroom->id);

            // For students, also load their submission for each assignment
            if ($this->isStudent && $this->studentInfo) {
                $query->with(['submissions' => function($q) {
                    $q->where('student_info_id', $this->studentInfo->id)
                      ->with('attachments');
                }]);
            }

            // Apply search filter
            if (!empty($this->search)) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'like', $searchTerm)
                      ->orWhere('description', 'like', $searchTerm)
                      ->orWhere('instructions', 'like', $searchTerm)
                      ->orWhere('assignment_type', 'like', $searchTerm)
                      ->orWhere('component_category', 'like', $searchTerm);
                });
            }

            $assignments = $query->latest()->paginate(10);
        }

        return view('livewire.assign.classwork', [
            'classroom' => $this->classroom,
            'assignments' => $assignments,
            'isStudent' => $this->isStudent,
            'studentInfo' => $this->studentInfo,
        ]);
    }

    /**
     * Create notifications for students when a new assignment is created.
     * 
     * @param Assignment $assignment
     * @param array $studentIds
     * @return void
     */
    protected function createAssignmentNotifications(Assignment $assignment, array $studentIds)
    {
        $creator = Auth::user();
        $creatorName = !empty($creator->first_name) || !empty($creator->last_name)
            ? trim(($creator->first_name ?? '') . ' ' . ($creator->last_name ?? ''))
            : ($creator->name ?? 'Teacher');

        // Get student info with user_id
        $students = \App\Models\StudentDetails\StudentInfo::whereIn('id', $studentIds)
            ->whereNotNull('user_id')
            ->get();

        foreach ($students as $student) {
            if ($student->user_id) {
                Notification::create([
                    'user_id' => $student->user_id,
                    'type' => 'assignment_created',
                    'title' => 'New Assignment',
                    'body' => "A new assignment has been posted: {$assignment->title} by {$creatorName}",
                    'url' => url('/assingments.index?id=' . $assignment->classroom_id . '#assignment-' . $assignment->id),
                    'notifiable_type' => Assignment::class,
                    'notifiable_id' => $assignment->id,
                    'data' => [
                        'assignment_id' => $assignment->id,
                        'assignment_title' => $assignment->title,
                        'classroom_id' => $assignment->classroom_id,
                        'creator_id' => $creator->id,
                        'creator_name' => $creatorName,
                        'due_date' => $assignment->due_date ? (\Carbon\Carbon::parse($assignment->due_date)->format('Y-m-d')) : null,
                    ],
                ]);
            }
        }
    }

    /**
     * Create notification for teacher when a student submits an assignment.
     * 
     * @param \App\Models\Grading\AssignmentSubmission $submission
     * @param Assignment $assignment
     * @return void
     */
    protected function createSubmissionNotification($submission, Assignment $assignment)
    {
        // Only notify if assignment has a creator (teacher)
        if (!$assignment->created_by) {
            return;
        }

        $student = $submission->studentInfo;
        if (!$student || !$student->user_id) {
            return;
        }

        $studentUser = $student->user;
        $studentName = $studentUser ? (
            !empty($studentUser->first_name) || !empty($studentUser->last_name)
                ? trim(($studentUser->first_name ?? '') . ' ' . ($studentUser->last_name ?? ''))
                : ($studentUser->name ?? 'Student')
        ) : 'Student';

        Notification::create([
            'user_id' => $assignment->created_by,
            'type' => 'submission_submitted',
            'title' => 'New Submission',
            'body' => "{$studentName} has submitted the assignment: {$assignment->title}",
            'url' => url('/assingments.index?id=' . $assignment->classroom_id),
            'notifiable_type' => \App\Models\Grading\AssignmentSubmission::class,
            'notifiable_id' => $submission->id,
            'data' => [
                'submission_id' => $submission->id,
                'assignment_id' => $assignment->id,
                'assignment_title' => $assignment->title,
                'student_id' => $student->id,
                'student_name' => $studentName,
                'classroom_id' => $assignment->classroom_id,
                'is_late' => $submission->is_late,
            ],
        ]);
    }

    /**
     * Create notifications for both student and teacher when a grade is saved.
     * 
     * @param \App\Models\Grading\AssignmentSubmission $submission
     * @return void
     */
    protected function createGradeNotifications($submission)
    {
        $assignment = $submission->assignment;
        $grader = Auth::user();
        $graderName = !empty($grader->first_name) || !empty($grader->last_name)
            ? trim(($grader->first_name ?? '') . ' ' . ($grader->last_name ?? ''))
            : ($grader->name ?? 'Teacher');

        // Notification for student
        $student = $submission->studentInfo;
        if ($student && $student->user_id) {
            $percentage = $assignment->points_possible && $submission->points_earned !== null
                ? round(($submission->points_earned / $assignment->points_possible) * 100, 2)
                : null;

            Notification::create([
                'user_id' => $student->user_id,
                'type' => 'submission_graded',
                'title' => 'Assignment Graded',
                'body' => "Your submission for '{$assignment->title}' has been graded: {$submission->points_earned}/{$assignment->points_possible} points" . ($percentage ? " ({$percentage}%)" : ''),
                'url' => url('/assingments.index?id=' . $assignment->classroom_id),
                'notifiable_type' => \App\Models\Grading\AssignmentSubmission::class,
                'notifiable_id' => $submission->id,
                'data' => [
                    'submission_id' => $submission->id,
                    'assignment_id' => $assignment->id,
                    'assignment_title' => $assignment->title,
                    'points_earned' => $submission->points_earned,
                    'points_possible' => $assignment->points_possible,
                    'percentage' => $percentage,
                    'grader_id' => $grader->id,
                    'grader_name' => $graderName,
                    'classroom_id' => $assignment->classroom_id,
                ],
            ]);
        }

        // Notification for teacher (confirmation) - only if grader is different from assignment creator
        if ($assignment->created_by && $assignment->created_by !== $grader->id) {
            $studentUser = $student->user ?? null;
            $studentName = $studentUser ? (
                !empty($studentUser->first_name) || !empty($studentUser->last_name)
                    ? trim(($studentUser->first_name ?? '') . ' ' . ($studentUser->last_name ?? ''))
                    : ($studentUser->name ?? 'Student')
            ) : 'Student';

            Notification::create([
                'user_id' => $assignment->created_by,
                'type' => 'submission_graded_by_other',
                'title' => 'Submission Graded',
                'body' => "{$graderName} has graded {$studentName}'s submission for '{$assignment->title}'",
                'url' => url('/assingments.index?id=' . $assignment->classroom_id),
                'notifiable_type' => \App\Models\Grading\AssignmentSubmission::class,
                'notifiable_id' => $submission->id,
                'data' => [
                    'submission_id' => $submission->id,
                    'assignment_id' => $assignment->id,
                    'assignment_title' => $assignment->title,
                    'student_id' => $student->id,
                    'student_name' => $studentName,
                    'grader_id' => $grader->id,
                    'grader_name' => $graderName,
                    'classroom_id' => $assignment->classroom_id,
                ],
            ]);
        }
    }
}
