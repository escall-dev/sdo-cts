# File Upload System Migration - Implementation Guide

## Overview
This guide explains the changes made to centralize file uploads and store only relative paths in the database.

## What Changed

### 1. **New Directory Structure**
Created centralized upload folders:
- `assets/uploads/images/` - For image files (JPG, JPEG, PNG)
- `assets/uploads/documents/` - For documents (PDF and other files)

### 2. **Database Schema Update**
Added `file_path` column to `complaint_documents` table to store relative paths.

**Migration SQL:** Run `database/migrate_add_file_path.sql`

```sql
ALTER TABLE complaint_documents 
ADD COLUMN file_path VARCHAR(500) NOT NULL DEFAULT '' 
AFTER file_name;
```

### 3. **File Naming Convention**
Files are now named using the pattern:
```
complaint_[complaint_id]_[category]_[timestamp]_[uniqueid].[extension]
```

Example: `complaint_1023_supporting_1736832000_abc123.pdf`

### 4. **Path Storage**
Only relative paths are stored in MySQL:
```
assets/uploads/images/complaint_1023_supporting_1736832000_abc123.jpg
assets/uploads/documents/complaint_1023_valid_id_1736832100_def456.pdf
```

## Updated Files

### Backend Files
1. **database/schema.sql** - Added file_path column to schema
2. **database/migrate_add_file_path.sql** - Migration script
3. **models/Complaint.php** - Updated addDocument() method to accept file_path parameter
4. **review.php** - Modified file upload logic to save to centralized folders with relative paths
5. **admin/complaint-view.php** - Updated all file URL references to use file_path column
6. **services/email/ComplaintNotification.php** - Updated attachment path resolution

### Key Changes in review.php
```php
// OLD: Saved to per-complaint folders
$uploadDir = __DIR__ . '/uploads/complaints/' . $complaintId . '/';

// NEW: Saves to centralized folders by file type
$imagesDir = __DIR__ . '/assets/uploads/images/';
$documentsDir = __DIR__ . '/assets/uploads/documents/';

// Determine target based on file extension
$isImage = in_array($ext, ['jpg', 'jpeg', 'png']);
$targetDir = $isImage ? $imagesDir : $documentsDir;
$targetRelativeDir = $isImage ? 'assets/uploads/images/' : 'assets/uploads/documents/';

// Create filename with complaint_id
$newFileName = "complaint_{$complaintId}_{$category}_{$timestamp}.{$ext}";
$relativePath = $targetRelativeDir . $newFileName;

// Store with relative path
$complaint->addDocument($complaintId, $newFileName, $originalName, $fileType, $fileSize, $category, $relativePath);
```

### Key Changes in admin/complaint-view.php
```php
// OLD: Hardcoded path
$fileUrl = "/CTS/uploads/complaints/" . $complaint['id'] . "/" . $doc['file_name'];

// NEW: Uses file_path column with fallback
$fileUrl = !empty($doc['file_path']) 
    ? "/CTS/" . $doc['file_path'] 
    : "/CTS/uploads/complaints/" . $complaint['id'] . "/" . $doc['file_name'];
```

## Benefits

### ✅ Portability
- Files work on all devices after repo pull or deployment
- No absolute device-specific paths
- Consistent file access across environments

### ✅ Organization
- Centralized file management
- Easy to backup and manage
- Clear separation of images vs documents

### ✅ Scalability
- Files linked by complaint_id
- Easy to implement file quotas or cleanup
- Better for CDN integration in future

### ✅ Backward Compatibility
- Old files still work through fallback logic
- No immediate migration of existing files required
- Gradual transition as new complaints are filed

## Migration Steps

### For Existing Installation:

1. **Run Database Migration**
   ```sql
   mysql -u root -p sdo_cts < database/migrate_add_file_path.sql
   ```

2. **Create New Directories** (already done by code)
   - `assets/uploads/images/`
   - `assets/uploads/documents/`

3. **Test New Complaint Submission**
   - Submit a test complaint with files
   - Verify files appear in centralized folders
   - Check file_path column in database
   - Confirm files display correctly in admin panel

4. **Optional: Migrate Existing Files**
   To migrate old files to new structure, you can run a migration script (not included).
   The system will continue to work with old files through fallback logic.

## File Categories

Files are categorized as:
- **handwritten_form** - Uploaded completed complaint forms
- **valid_id** - Government IDs and credentials
- **supporting** - Supporting documents and evidence

## Security Notes

- File uploads are validated for allowed extensions
- Files are renamed to prevent conflicts and security issues
- Only relative paths are stored in database
- Direct file access is through web server configuration

## Support

For issues or questions:
- Check error logs in PHP error log
- Verify folder permissions (775 or 755)
- Ensure database migration was successful
- Test with a fresh complaint submission

---

**Last Updated:** January 13, 2026
**Version:** 1.0
