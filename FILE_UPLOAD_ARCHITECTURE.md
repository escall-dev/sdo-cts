# File Upload System Architecture

## System Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    COMPLAINT FORM SUBMISSION                    │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│               User Uploads Files (index.php)                    │
│  • Supporting Documents (PDF, JPG, PNG)                         │
│  • Valid ID/Credentials                                         │
│  • Handwritten Forms (Optional)                                 │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                    ┌─────────────────┐
                    │  uploads/temp/  │ ← Temporary storage
                    └─────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│              Review & Submit (review.php)                       │
│  • Creates complaint record                                     │
│  • Processes each file                                          │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                    ┌─────────────────┐
                    │  File Type?     │
                    └─────────────────┘
                    ↙                 ↘
        ┌──────────────┐        ┌──────────────────┐
        │  Image File  │        │  Document File   │
        │ (.jpg, .png) │        │  (.pdf, etc)     │
        └──────────────┘        └──────────────────┘
                ↓                        ↓
    ┌────────────────────┐   ┌────────────────────────┐
    │ assets/uploads/    │   │ assets/uploads/        │
    │     images/        │   │    documents/          │
    └────────────────────┘   └────────────────────────┘
                ↓                        ↓
        complaint_[id]_[category]_[timestamp]_[unique].[ext]
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    DATABASE STORAGE                             │
│  complaint_documents table:                                     │
│  ├─ file_name: complaint_1023_supporting_123456.jpg            │
│  ├─ file_path: assets/uploads/images/complaint_1023...jpg      │
│  ├─ original_name: evidence-photo.jpg                          │
│  └─ category: supporting                                        │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                    FILE RETRIEVAL                               │
│  • Admin views complaint (complaint-view.php)                   │
│  • Email notifications (ComplaintNotification.php)              │
│  • Uses file_path from database                                 │
│  • Fallback to old structure if file_path empty                 │
└─────────────────────────────────────────────────────────────────┘
```

## Directory Structure

```
CTS/
│
├── assets/
│   └── uploads/                    ← NEW CENTRALIZED LOCATION
│       ├── images/                 ← All images (JPG, PNG)
│       │   ├── .gitkeep
│       │   ├── complaint_1023_supporting_1736832000_abc.jpg
│       │   ├── complaint_1023_valid_id_1736832100_def.png
│       │   └── complaint_1024_handwritten_form_1736832200_ghi.jpg
│       │
│       └── documents/              ← All documents (PDF, etc)
│           ├── .gitkeep
│           ├── complaint_1023_supporting_1736832300_jkl.pdf
│           └── complaint_1024_valid_id_1736832400_mno.pdf
│
├── uploads/
│   ├── temp/                       ← Temporary staging area
│   │   ├── .gitkeep
│   │   └── [session_id]_[unique].[ext]  (deleted after submission)
│   │
│   └── complaints/                 ← OLD STRUCTURE (deprecated)
│       ├── .gitkeep
│       └── [complaint_id]/         (still works for old files)
│           └── [files...]
│
└── database/
    ├── schema.sql                  ← Updated schema with file_path
    └── migrate_add_file_path.sql   ← Migration script
```

## File Naming Convention

```
complaint_[complaint_id]_[category]_[timestamp]_[unique_id].[extension]
    ↓            ↓            ↓           ↓            ↓
    │            │            │           │            └─ File extension
    │            │            │           └─ Unique identifier (uniqid())
    │            │            └─ Unix timestamp (time())
    │            └─ Category: supporting, valid_id, handwritten_form
    └─ Complaint ID from database

Example: complaint_1023_supporting_1736832000_65a2b3c4d5e6f.jpg
```

## Database Schema

```sql
complaint_documents
├── id (INT) PRIMARY KEY AUTO_INCREMENT
├── complaint_id (INT) FOREIGN KEY → complaints(id)
├── file_name (VARCHAR) - Physical filename on disk
├── file_path (VARCHAR) - ★ NEW: Relative path from project root
├── original_name (VARCHAR) - Original uploaded filename
├── file_type (VARCHAR) - MIME type
├── file_size (INT) - Size in bytes
├── category (VARCHAR) - supporting, valid_id, handwritten_form
└── upload_date (TIMESTAMP)

Example Record:
{
  "id": 42,
  "complaint_id": 1023,
  "file_name": "complaint_1023_supporting_1736832000_abc123.jpg",
  "file_path": "assets/uploads/images/complaint_1023_supporting_1736832000_abc123.jpg",
  "original_name": "evidence-photo.jpg",
  "file_type": "image/jpeg",
  "file_size": 245760,
  "category": "supporting",
  "upload_date": "2026-01-13 14:30:00"
}
```

## URL Generation Logic

```php
// In admin/complaint-view.php and other views

// NEW APPROACH - Uses relative path from database
$fileUrl = !empty($doc['file_path'])
    ? "/CTS/" . $doc['file_path']
    : "/CTS/uploads/complaints/" . $complaint['id'] . "/" . $doc['file_name'];

// Examples:
// New file: /CTS/assets/uploads/images/complaint_1023_supporting_123.jpg
// Old file: /CTS/uploads/complaints/1023/original_filename.jpg (fallback)
```

## Advantages of New System

```
┌─────────────────────────────────────────────────────────────────┐
│                    OLD SYSTEM (Deprecated)                      │
├─────────────────────────────────────────────────────────────────┤
│ ❌ Files stored per complaint: uploads/complaints/[id]/        │
│ ❌ Mixed images and documents in same folder                    │
│ ❌ Harder to manage and backup                                  │
│ ❌ No standardized naming convention                            │
│ ❌ Path construction requires complaint_id lookup               │
└─────────────────────────────────────────────────────────────────┘
                              ↓ MIGRATION
┌─────────────────────────────────────────────────────────────────┐
│                    NEW SYSTEM (Current)                         │
├─────────────────────────────────────────────────────────────────┤
│ ✅ Centralized: assets/uploads/images/ & documents/            │
│ ✅ Images and documents separated                               │
│ ✅ Easy backup and CDN integration                              │
│ ✅ Standardized naming: complaint_[id]_[category]_[time]       │
│ ✅ Direct path from database (no construction needed)           │
│ ✅ Portable across devices and deployments                      │
│ ✅ Backward compatible with old files                           │
└─────────────────────────────────────────────────────────────────┘
```

## File Categories

```
┌──────────────────────┬────────────────────────────────────────┐
│ Category             │ Description                            │
├──────────────────────┼────────────────────────────────────────┤
│ handwritten_form     │ Scanned/photographed completed forms   │
│ valid_id             │ Government IDs and credentials         │
│ supporting           │ Evidence, documents, supporting files  │
└──────────────────────┴────────────────────────────────────────┘
```

## Security Considerations

```
✅ File validation
   ├─ Allowed extensions: PDF, JPG, JPEG, PNG
   ├─ File size limits enforced
   └─ MIME type checking

✅ Secure naming
   ├─ Original filename not used directly
   ├─ Timestamp + unique ID prevents conflicts
   └─ No user input in filenames

✅ Access control
   ├─ Admin authentication required
   ├─ No direct directory listing
   └─ Files served through PHP (future: add authentication layer)

✅ Database integrity
   ├─ Foreign key constraints
   ├─ Relative paths only
   └─ No absolute device paths
```

---

**Architecture Version:** 2.0
**Last Updated:** January 13, 2026
