# Land Report Management Backend Implementation

## Overview
This document describes the complete backend implementation for the Land Report Management functionality used by the Operational Manager role in the FARMMASTER system.

## Database Schema
The implementation uses the existing `land_report` table with the following key fields:
- `report_id`: Primary key
- `land_id`: Foreign key to land table
- `user_id`: Foreign key to user table (landowner)
- `status`: Report status ('', 'Approved', 'Rejected')
- `environmental_notes`: Multi-purpose field for storing supervisor assignments and review feedback
- Other fields for soil analysis data (ph_value, organic_matter, etc.)

## API Endpoints

### Assignment Management

#### 1. Get Assignment Reports
**Endpoint:** `GET /land-reports/assignments` (authenticated) or `/land-reports/assignments-public` (public)
**Purpose:** Retrieve land reports for supervisor assignment management
**Response Format:**
```json
{
  "status": "success",
  "message": "Assignment reports retrieved successfully",
  "data": [
    {
      "id": "#2025-LR-001",
      "report_id": 1,
      "location": "Kandy",
      "name": "John Doe",
      "date": "2025-02-11",
      "supervisor": "Mr. Silva" | "Unassigned",
      "status": "Assigned" | "Unassigned",
      "current_status": "Assigned" | "Approved" | "Rejected",
      "land_id": 19,
      "user_id": 32
    }
  ]
}
```

#### 2. Get Available Supervisors
**Endpoint:** `GET /land-reports/supervisors` (authenticated) or `/land-reports/supervisors-public` (public)
**Purpose:** Get list of available field supervisors for assignment
**Response Format:**
```json
{
  "status": "success",
  "message": "Available supervisors retrieved successfully",
  "data": [
    {
      "user_id": 31,
      "first_name": "Kanchana",
      "last_name": "Almeda",
      "email": "fs@gmail.com",
      "phone": "+94274836477",
      "full_name": "Kanchana Almeda",
      "role": "Field Supervisor",
      "assignment_status": "Available"
    }
  ]
}
```

#### 3. Assign Supervisor
**Endpoint:** `PUT /land-reports/{id}/assign` (authenticated) or `/land-reports/{id}/assign-public` (public)
**Purpose:** Assign or reassign a field supervisor to a land report
**Request Body:**
```json
{
  "supervisor_name": "Kanchana Almeda",
  "supervisor_id": "31"
}
```
**Response:**
```json
{
  "status": "success",
  "message": "Supervisor assigned successfully"
}
```

### Review Management

#### 4. Get Review Reports
**Endpoint:** `GET /land-reports/reviews` (authenticated) or `/land-reports/reviews-public` (public)
**Purpose:** Retrieve completed land reports for operational manager review
**Response Format:**
```json
{
  "status": "success",
  "message": "Review reports retrieved successfully",
  "data": [
    {
      "id": "#2025-LR-001",
      "report_id": 1,
      "location": "Kandy",
      "name": "John Doe",
      "supervisorId": "SR0031",
      "supervisor": "Kanchana Almeda",
      "status": "Not Reviewed" | "Approved" | "Rejected",
      "land_id": 19,
      "user_id": 32,
      "report_details": {
        "land_description": "Well-drained sandy loam soil...",
        "crop_recommendation": "Rice cultivation recommended...",
        "ph_value": 6.5,
        "organic_matter": 4.2,
        "nitrogen_level": "Medium",
        "phosphorus_level": "High",
        "potassium_level": "Medium",
        "environmental_notes": "Assigned to: Kanchana Almeda (ID: 31)"
      }
    }
  ]
}
```

#### 5. Submit Review
**Endpoint:** `PUT /land-reports/{id}/review` (authenticated) or `/land-reports/{id}/review-public` (public)
**Purpose:** Submit review decision (approve/reject) with feedback
**Request Body:**
```json
{
  "decision": "Approve" | "Request Revisions",
  "feedback": "Optional feedback text"
}
```
**Response:**
```json
{
  "status": "success",
  "message": "Review submitted successfully",
  "data": {
    "report_id": 1,
    "decision": "Approve",
    "feedback": "Report meets all quality standards"
  }
}
```

## Controller Methods

### LandReportController.php
New methods added:
- `getAssignmentReports()` / `getAssignmentReportsPublic()`
- `getReviewReports()` / `getReviewReportsPublic()`
- `submitReview($reportId)` / `submitReviewPublic($reportId)`

Existing methods enhanced:
- `assignSupervisor($reportId)` / `assignSupervisorPublic($reportId)`
- `getAvailableSupervisors()` / `getAvailableSupervisorsPublic()`

## Model Methods

### LandReportModel.php
New methods added:
- `getAssignmentReports()`: Returns formatted data for assignment management
- `getReviewReports()`: Returns formatted data for review management
- `submitReview($reportId, $decision, $feedback)`: Updates report status and adds review feedback

Enhanced methods:
- `assignSupervisor($reportId, $supervisorName, $supervisorId)`: Assigns supervisor using environmental_notes
- `getAvailableSupervisors()`: Returns supervisors not currently assigned to pending reports

## Frontend Integration

### Assignment Management
The frontend can use these endpoints to:
1. Display list of land reports with assignment status
2. Show available supervisors for assignment
3. Assign/reassign supervisors to reports
4. Filter reports by assignment status

### Review Management
The frontend can use these endpoints to:
1. Display list of completed reports awaiting review
2. Show detailed report information for review
3. Submit approval/rejection decisions with feedback
4. Track review status

## Data Flow

### Assignment Process
1. **Payment**: Landowner pays for land assessment
2. **Assignment**: Operational Manager assigns field supervisor via `/assign` endpoint
3. **Field Work**: Supervisor conducts assessment and submits report
4. **Review Ready**: Report becomes available in `/reviews` endpoint

### Review Process
1. **Review**: Operational Manager reviews report via `/reviews` endpoint
2. **Decision**: Submit approval/rejection via `/review` endpoint
3. **Status Update**: Report status updated to 'Approved' or 'Rejected'
4. **Feedback**: Review feedback stored in environmental_notes

## Security Features
- Authentication required for standard endpoints
- Public endpoints available for testing (suffix: `-public`)
- Role-based access control (Operational_Manager role required)
- Input validation and sanitization
- SQL injection protection via prepared statements

## Error Handling
All endpoints include comprehensive error handling:
- 400: Bad Request (missing/invalid data)
- 401: Unauthorized (authentication required)
- 403: Forbidden (insufficient permissions)
- 404: Not Found (resource doesn't exist)
- 500: Internal Server Error (database/system errors)

## Testing
Use the included `test_land_reports.php` script to test all endpoints:
```bash
php test_land_reports.php
```

## Database Optimizations
The implementation efficiently uses the existing schema:
- **environmental_notes** field stores supervisor assignments and review feedback
- **status** field tracks approval status
- Indexes on user_id, land_id, and status fields optimize queries
- JOIN operations minimize database calls

## Future Enhancements
Potential improvements:
1. Add dedicated supervisor assignment tracking table
2. Implement email notifications for assignments/reviews
3. Add detailed audit trail for status changes
4. Create dashboard analytics for assignment/review metrics