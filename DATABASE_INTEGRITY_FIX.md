# Database Referential Integrity Fix Summary

## Issue Identified
When land records were deleted from the database, proposals and harvest income were not showing because the models used INNER JOINs which required the referenced land records to exist.

## Root Cause
- **ProposalModel**: Used `JOIN land l ON p.land_id = l.land_id` 
- **HarvestModel**: Used `JOIN land l ON h.land_id = l.land_id`
- **LandReportModel**: Used `JOIN land l ON lr.land_id = l.land_id`

When land records were deleted, these JOINs would exclude any proposals, harvests, or reports referencing the deleted lands.

## Fixes Applied

### 1. ProposalModel.php
- Changed all `JOIN land` to `LEFT JOIN land`
- Added `COALESCE(l.location, 'Land Deleted')` for location field
- Added `COALESCE(l.size, 0)` for size field
- Updated search queries to handle null land data with `COALESCE(l.location, '')`

**Files affected:**
- `getUserProposals()` method
- `getAllProposals()` method  
- `getProposalById()` method
- `searchProposals()` method

### 2. HarvestModel.php
- Changed all `JOIN land` to `LEFT JOIN land`
- Added `COALESCE(l.location, 'Land Deleted')` for location field
- Added `COALESCE(l.size, 0)` for size field
- Updated search queries to handle null land data

**Files affected:**
- `getUserHarvests()` method
- `getAllHarvests()` method
- `getHarvestById()` method  
- `searchHarvests()` method

### 3. LandReportModel.php
- Changed all `JOIN land` to `LEFT JOIN land`
- Added `COALESCE(l.location, 'Land Deleted')` for location field
- Added `COALESCE(l.size, 0)` for size field
- Added `COALESCE(l.payment_status, 'unknown')` for payment status
- Updated WHERE clauses to handle null land data

**Files affected:**
- `getAllReports()` method
- `getReportById()` method
- `getAllLandReportsAndAssignments()` method
- `getAssignedReports()` method
- `getInterestRequests()` method

## Benefits of the Fix

1. **Data Preservation**: Proposals, harvests, and reports are no longer lost when land records are deleted
2. **Better User Experience**: Users can still see their historical data even if land was deleted
3. **Data Integrity**: The system gracefully handles missing reference data
4. **Clear Indication**: Shows "Land Deleted" instead of hiding the records entirely

## Recommendations for Future

### 1. Database Constraints
Consider adding proper foreign key constraints with appropriate actions:

```sql
ALTER TABLE proposals 
ADD CONSTRAINT fk_proposals_land 
FOREIGN KEY (land_id) REFERENCES land(land_id) 
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE harvest 
ADD CONSTRAINT fk_harvest_land 
FOREIGN KEY (land_id) REFERENCES land(land_id) 
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE land_report 
ADD CONSTRAINT fk_land_report_land 
FOREIGN KEY (land_id) REFERENCES land(land_id) 
ON DELETE SET NULL ON UPDATE CASCADE;
```

### 2. Soft Delete Implementation
Instead of hard deleting land records, implement soft delete:

```sql
ALTER TABLE land ADD COLUMN deleted_at TIMESTAMP NULL;
```

Then modify queries to exclude soft-deleted records:
```sql
WHERE (l.deleted_at IS NULL OR l.deleted_at = '0000-00-00 00:00:00')
```

### 3. Data Cleanup Script
Create a script to handle orphaned records and maintain data consistency.

### 4. Frontend Updates
Update frontend components to handle "Land Deleted" cases gracefully and possibly show different styling for deleted land references.

## Testing Recommendations
1. Test proposal listings after land deletion
2. Test harvest income reports after land deletion  
3. Test land report functionality after land deletion
4. Verify that new proposals/harvests can still be created for existing lands
5. Test search functionality with deleted land references

This fix ensures that your application maintains data visibility and integrity even when land records are removed from the database.