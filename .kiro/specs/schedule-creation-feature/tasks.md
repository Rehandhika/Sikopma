# Implementation Plan - Schedule Creation Feature

## Overview

Implementation akan dilakukan secara incremental dengan fokus pada MVP (Minimum Viable Product) terlebih dahulu, kemudian enhancement features. Setiap task dirancang untuk dapat ditest secara independen.

## Phase 1: Foundation & Core Models (Priority: CRITICAL)

- [x] 1. Database Schema & Models



  - Create migrations for new tables
  - Enhance existing models with new methods
  - Create new models (ScheduleTemplate, AssignmentHistory)
  - Add relationships and scopes
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_



- [ ] 1.1 Create schedule_templates migration
  - Create migration file for schedule_templates table
  - Add columns: name, description, created_by, pattern (JSON), is_public, usage_count
  - Add indexes for performance
  - Add foreign key constraints


  - _Requirements: 4.1, 4.2_

- [ ] 1.2 Create assignment_histories migration
  - Create migration file for assignment_histories table
  - Add columns: schedule_id, action, assignment_data (JSON), performed_by, performed_at


  - Add indexes for schedule_id and performed_at
  - Add foreign key constraints
  - _Requirements: 11.1, 11.2_



- [ ] 1.3 Enhance schedules table
  - Add migration to add new columns: published_by, total_slots, filled_slots, coverage_rate
  - Update existing schedules to calculate and populate these fields
  - _Requirements: 1.5, 7.4, 8.3_



- [ ] 1.4 Create ScheduleTemplate model
  - Create model file with fillable fields
  - Add casts for pattern (array) and is_public (boolean)
  - Add relationship to User (creator)


  - Add method incrementUsage()
  - _Requirements: 4.1, 4.2, 4.3_

- [ ] 1.5 Create AssignmentHistory model
  - Create model file with fillable fields


  - Add casts for assignment_data (array) and performed_at (datetime)
  - Add relationship to Schedule
  - Add method revert() for undo functionality
  - _Requirements: 11.1, 11.2, 11.3_


- [ ] 1.6 Enhance Schedule model
  - Add new methods: getAssignmentGrid(), calculateCoverage(), canPublish()
  - Add accessor for coverageRate
  - Update canEdit() to check published_by
  - Add scope for unpublished schedules
  - _Requirements: 1.5, 6.2, 7.1, 7.2_



- [ ] 1.7 Enhance ScheduleAssignment model
  - Add method isAvailableForSwap()
  - Add accessor for conflictStatus
  - Add scope for unassigned slots
  - Update relationships if needed
  - _Requirements: 2.3, 5.1, 5.2_



## Phase 2: Service Layer (Priority: HIGH)

- [ ] 2. Core Services Implementation
  - Create service classes for business logic
  - Implement validation and conflict detection
  - Implement auto-assignment algorithm
  - Add comprehensive error handling
  - _Requirements: 2.1, 3.1, 5.1, 5.2, 5.3_



- [ ] 2.1 Create ScheduleService
  - Create app/Services/ScheduleService.php
  - Implement createSchedule() method with validation
  - Implement addAssignment() with conflict check
  - Implement removeAssignment() method
  - Implement publishSchedule() with validation and notifications
  - Implement calculateStatistics() method


  - Implement copyFromPreviousWeek() method
  - _Requirements: 1.1, 1.2, 2.1, 2.2, 7.1, 7.2, 7.3, 9.1, 9.2_

- [ ] 2.2 Create ConflictDetectionService
  - Create app/Services/ConflictDetectionService.php
  - Implement detectConflicts() method (multi-level detection)

  - Implement checkDoubleAssignments() method
  - Implement checkAvailabilityMismatches() method
  - Implement checkInactiveUsers() method
  - Implement checkOverloadedUsers() method
  - Implement suggestAlternatives() method
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 2.3 Create AutoAssignmentService
  - Create app/Services/AutoAssignmentService.php
  - Implement generateAssignments() main method
  - Implement getUserAvailability() method
  - Implement calculateOptimalDistribution() method
  - Implement selectBestUser() with scoring algorithm
  - Implement calculateFairnessScore() method
  - Add configuration for weights (fairness vs availability)
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [ ] 2.4 Create TemplateService
  - Create app/Services/TemplateService.php
  - Implement createTemplate() method
  - Implement applyTemplate() method with date adjustment
  - Implement listTemplates() method
  - Implement deleteTemplate() method (soft delete)
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

- [ ] 2.5 Create ScheduleExportService


  - Create app/Services/ScheduleExportService.php
  - Implement exportToPdf() using DomPDF or similar
  - Implement exportToExcel() using Laravel Excel
  - Create PDF template view
  - Create Excel formatting logic
  - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

## Phase 3: Livewire Components (Priority: HIGH)

- [ ] 3. Main Schedule Creation Component



  - Create CreateSchedule Livewire component
  - Implement grid-based UI
  - Add real-time validation
  - Integrate with services
  - _Requirements: 1.1, 2.1, 2.2, 6.1, 6.2_


- [x] 3.1 Create CreateSchedule component



  - Create app/Livewire/Schedule/CreateSchedule.php
  - Define all public properties (weekStartDate, assignments, etc.)
  - Implement mount() method with initialization
  - Implement initializeGrid() to create 4x3 grid structure
  - _Requirements: 1.1, 1.2, 1.3_


- [x] 3.2 Implement manual assignment methods




  - Implement selectCell() to show user selector
  - Implement assignUser() with validation
  - Implement removeAssignment() method
  - Add real-time conflict detection
  - Update statistics after each change
  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [x] 3.3 Implement auto-assignment integration




  - Implement autoAssign() method calling AutoAssignmentService
  - Implement previewAutoAssignment() to show preview modal
  - Add loading state during auto-assignment
  - Handle errors gracefully
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 3.4 Implement bulk operations





  - Implement assignToAllSessions() method
  - Implement assignToAllDays() method
  - Implement clearAll() with confirmation
  - Implement clearDay() with confirmation
  - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [x] 3.5 Implement undo/redo functionality




  - Implement saveToHistory() to track changes
  - Implement undo() method
  - Implement redo() method
  - Add UI indicators for undo/redo availability
  - Limit history to 20 steps
  - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_



- [x] 3.6 Implement save and publish



  - Implement saveDraft() method
  - Implement validateSchedule() before publish
  - Implement publish() method with ScheduleService
  - Add success/error notifications
  - Redirect after publish
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_
-

- [x] 3.7 Create component view




  - Create resources/views/livewire/schedule/create-schedule.blade.php
  - Implement grid layout with Tailwind CSS
  - Add date/session headers
  - Create assignment cell component
  - Add statistics sidebar
  - Add action buttons (save, publish, etc.)
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

## Phase 4: Supporting Components (Priority: MEDIUM)

- [x] 4. Additional UI Components







  - Create reusable components
  - Implement modals and dialogs
  - Add statistics visualization
  - _Requirements: 2.1, 6.1, 8.1, 8.2_


- [x] 4.1 Create AssignmentCell component

  - Create app/Livewire/Schedule/AssignmentCell.php
  - Accept props: date, session, assignment, availableUsers
  - Implement click to assign/edit
  - Show user info with avatar
  - Show availability indicator
  - Show conflict warning
  - _Requirements: 2.1, 2.2, 5.4_


- [x] 4.2 Create UserSelector modal component

  - Create app/Livewire/Schedule/UserSelector.php
  - Show list of users grouped by availability
  - Add search functionality
  - Show user statistics (current shifts, availability)
  - Highlight conflicts
  - _Requirements: 2.1, 2.2, 2.3_



- [x] 4.3 Create ScheduleStatistics component

  - Create app/Livewire/Schedule/ScheduleStatistics.php
  - Display total assignments and coverage
  - Show assignments per user (bar chart)
  - Show distribution per session
  - Show fairness score
  - Update in real-time

  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 4.4 Create PreviewSchedule component

  - Create app/Livewire/Schedule/PreviewSchedule.php
  - Show full week calendar view
  - Display all assignments with user info
  - Show statistics summary
  - Allow inline editing
  - Add print button
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_



- [ ] 4.5 Create TemplateSelector modal
  - Create app/Livewire/Schedule/TemplateSelector.php
  - List available templates
  - Show template preview
  - Add search and filter
  - Show usage count
  - _Requirements: 4.3, 4.4_

## Phase 5: Template & Copy Features (Priority: MEDIUM)

- [ ] 5. Template and Copy Functionality
  - Implement template creation and application
  - Implement copy from previous week
  - Add template management UI
  - _Requirements: 4.1, 4.2, 4.3, 9.1, 9.2_

- [ ] 5.1 Implement save as template
  - Add saveAsTemplate() method in CreateSchedule
  - Create modal for template name and description
  - Call TemplateService to save
  - Show success notification
  - _Requirements: 4.1, 4.2_

- [ ] 5.2 Implement load template
  - Add loadTemplate() method in CreateSchedule
  - Show template selector modal
  - Apply template using TemplateService
  - Validate users still active
  - Show warnings for inactive users
  - _Requirements: 4.3, 4.4, 4.5_

- [ ] 5.3 Implement copy from previous week
  - Add copyFromPreviousWeek() method
  - Show week selector modal
  - Call ScheduleService to copy
  - Adjust dates automatically
  - Highlight conflicts for review
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

## Phase 6: Export & Print (Priority: LOW)

- [ ] 6. Export and Print Features
  - Implement PDF export
  - Implement Excel export
  - Create print-friendly views
  - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

- [ ] 6.1 Implement PDF export
  - Install DomPDF package
  - Create PDF template view
  - Implement exportToPdf() in CreateSchedule
  - Format schedule for A4 paper
  - Include statistics and metadata
  - _Requirements: 12.1, 12.3, 12.4, 12.5_

- [ ] 6.2 Implement Excel export
  - Install Laravel Excel package
  - Create Excel export class
  - Implement exportToExcel() in CreateSchedule
  - Format with proper headers and styling
  - Include multiple sheets (schedule, statistics)
  - _Requirements: 12.2, 12.4_

- [ ] 6.3 Create print view
  - Create print-specific CSS
  - Implement print preview
  - Optimize for printing
  - _Requirements: 12.3, 12.5_

## Phase 7: Testing (Priority: HIGH)

- [ ] 7. Comprehensive Testing
  - Write unit tests for services
  - Write feature tests for components
  - Write integration tests
  - Test edge cases and error scenarios
  - _Requirements: All_

- [ ]* 7.1 Unit tests for ScheduleService
  - Test createSchedule() with valid/invalid dates
  - Test addAssignment() with conflicts
  - Test removeAssignment()
  - Test publishSchedule() validation
  - Test calculateStatistics()
  - Test copyFromPreviousWeek()
  - _Requirements: 1.1, 2.1, 7.1, 9.1_

- [ ]* 7.2 Unit tests for AutoAssignmentService
  - Test generateAssignments() with various scenarios
  - Test fair distribution algorithm
  - Test availability weighting
  - Test conflict avoidance
  - Test edge cases (insufficient users, no availability)
  - _Requirements: 3.1, 3.2, 3.3, 3.4_

- [ ]* 7.3 Unit tests for ConflictDetectionService
  - Test detectConflicts() for all conflict types
  - Test suggestAlternatives()
  - Test conflict resolution
  - _Requirements: 5.1, 5.2, 5.3_

- [ ]* 7.4 Feature tests for CreateSchedule component
  - Test schedule creation flow
  - Test manual assignment
  - Test auto-assignment
  - Test undo/redo
  - Test save draft
  - Test publish
  - _Requirements: 1.1, 2.1, 3.1, 7.1, 11.1_

- [ ]* 7.5 Integration tests
  - Test complete workflow (create → assign → publish)
  - Test template workflow
  - Test copy workflow
  - Test export workflow
  - _Requirements: All_

## Phase 8: Documentation & Polish (Priority: LOW)

- [ ] 8. Documentation and Final Polish
  - Write user documentation
  - Add inline help
  - Optimize performance
  - Fix UI/UX issues
  - _Requirements: All_

- [ ]* 8.1 Create user documentation
  - Write step-by-step guide for creating schedule
  - Document auto-assignment algorithm
  - Document template usage
  - Add screenshots and examples
  - _Requirements: All_

- [ ]* 8.2 Add inline help
  - Add tooltips for all buttons
  - Add help icons with explanations
  - Create FAQ section
  - _Requirements: All_

- [ ]* 8.3 Performance optimization
  - Add caching for availability data
  - Optimize database queries
  - Add lazy loading for large datasets
  - Implement pagination if needed
  - _Requirements: All_

- [ ]* 8.4 UI/UX polish
  - Add loading states
  - Add animations for better UX
  - Improve mobile responsiveness
  - Add keyboard shortcuts
  - _Requirements: All_

## Notes

- Phases 1-3 are MVP (Minimum Viable Product)
- Phases 4-6 are enhancements
- Phase 7 (Testing) should be done incrementally after each phase
- Phase 8 can be done in parallel with other phases
- Tasks marked with * are optional but recommended
- Each task should be tested before moving to the next
- Use feature flags for gradual rollout if needed

## Estimated Timeline

- Phase 1: 2-3 days
- Phase 2: 3-4 days
- Phase 3: 4-5 days
- Phase 4: 2-3 days
- Phase 5: 2 days
- Phase 6: 1-2 days
- Phase 7: 3-4 days (ongoing)
- Phase 8: 1-2 days

**Total: 18-25 days** (3-4 weeks for complete implementation)

**MVP (Phases 1-3): 9-12 days** (1.5-2 weeks)
