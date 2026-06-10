# Deployment Instructions - Complainant Information Enhancement

## Step 1: Database Migration

Before deploying the code changes, you must run the database migration to add the three new columns.

### Option A: Using MySQL CLI
```bash
cd /path/to/CTS
mysql -u root -p sdo_cts < database/migration_add_complaint_contact_fields.sql
```

### Option B: Using phpMyAdmin
1. Go to phpMyAdmin (usually http://localhost/phpmyadmin)
2. Select the `sdo_cts` database
3. Click the "SQL" tab
4. Copy and paste the contents of `database/migration_add_complaint_contact_fields.sql`
5. Click "Go"

### Option C: Direct MySQL Command
```sql
USE sdo_cts;

ALTER TABLE complaints 
ADD COLUMN complaint_name VARCHAR(255) DEFAULT NULL AFTER printed_name_pangalan,
ADD COLUMN complaint_email VARCHAR(255) DEFAULT NULL AFTER complaint_name,
ADD COLUMN complaint_contact VARCHAR(20) DEFAULT NULL AFTER complaint_email;

ALTER TABLE complaints 
ADD INDEX idx_complaint_email (complaint_email),
ADD INDEX idx_complaint_name (complaint_name);
```

## Step 2: Deploy Code Files

Deploy the following modified files:
1. `review.php` - Updated with new form fields and validation
2. `models/Complaint.php` - Updated model to store new fields
3. `services/email/ComplaintNotification.php` - Enhanced email logic with validation
4. `services/email/templates/complaint_submitted_complainant.html` - Updated template
5. `services/email/templates/complaint_submitted_admin.html` - New admin template

## Step 3: Test the Implementation

### Manual Testing Steps:

1. **Test Form Validation**
   - Navigate to the complaint submission form
   - Go to the review page
   - Try to submit without filling Name → Should show error
   - Try to submit without filling Email → Should show error
   - Try to submit without filling Contact → Should show error
   - Try to submit with invalid email (e.g., "abc") → Should show error
   - Try to submit with contact number < 7 digits → Should show error

2. **Test Successful Submission**
   - Fill in all three fields correctly:
     - Name: John Doe
     - Email: john@example.com
     - Contact: 09171234567
   - Click "Confirm & Submit"
   - Verify submission succeeds and redirects to success page

3. **Verify Database Storage**
   - Use phpMyAdmin or MySQL CLI to check the complaints table
   - Verify the three new fields contain the submitted values
   - Example query:
   ```sql
   SELECT complaint_name, complaint_email, complaint_contact FROM complaints WHERE id = [complaint_id];
   ```

4. **Test Email Notifications**
   - Check the complainant's email inbox for the submission confirmation
   - Verify the email contains:
     - Reference number
     - Correct complaint name
     - Correct email address
     - Correct contact number
     - "Complainant Information" section with all three fields
   
   - Check the admin's email inbox
   - Verify the email contains:
     - Reference number
     - Same complainant information as above
     - Complaint preview
     - Link to admin panel

5. **Test Edge Cases**
   - Manually update database to set complaint_email to NULL
   - Trigger a status change notification (using admin panel)
   - Verify email is not sent (check logs)
   - Check error log for: "Email notification skipped: Missing required complainant information"

### Automated Testing (if applicable):
```php
// Test file location: tests/ComplaintNotificationTest.php
// Test that notification is skipped when fields are missing
$complaintData = [
    'complaint_name' => 'John Doe',
    'complaint_email' => null, // Missing email
    'complaint_contact' => '09171234567'
];

$notification = new ComplaintNotification();
$result = $notification->sendComplaintSubmittedNotification($complaintData);
// Assert $result === false
```

## Step 4: Monitor Deployment

1. **Check Error Logs**
   ```bash
   tail -f /path/to/error_log.txt
   ```
   Look for any PHP errors related to the new fields

2. **Monitor Email Sending**
   - Check that emails are being sent successfully
   - Monitor for "Email notification error:" messages in logs
   - Verify complainant information appears in all sent emails

3. **Database Integrity**
   - Run monthly checks to ensure all new complaints have values in the three new fields
   - Query to check for NULL values:
   ```sql
   SELECT id, reference_number FROM complaints 
   WHERE complaint_name IS NULL OR complaint_email IS NULL OR complaint_contact IS NULL;
   ```

## Rollback Plan (if needed)

If issues occur, you can rollback the database changes:

```sql
USE sdo_cts;

-- Remove the indexes first
ALTER TABLE complaints DROP INDEX idx_complaint_email;
ALTER TABLE complaints DROP INDEX idx_complaint_name;

-- Remove the columns
ALTER TABLE complaints DROP COLUMN complaint_name;
ALTER TABLE complaints DROP COLUMN complaint_email;
ALTER TABLE complaints DROP COLUMN complaint_contact;
```

Then revert the code files to the previous version.

## Performance Considerations

- Added 2 indexes (complaint_email, complaint_name) for faster lookups
- NULL values in new fields won't significantly impact existing queries
- Recommended to run database optimization after migration:
  ```sql
  OPTIMIZE TABLE complaints;
  ```

## Support & Troubleshooting

### Issue: "Undefined column" error when submitting
**Solution:** Run the database migration (Step 1) first

### Issue: Emails not being sent
**Solution:** 
1. Check error logs for missing field values
2. Verify SMTP configuration in config/mail_config.php
3. Test with a simple PHP script to ensure email works

### Issue: Form not validating on client-side
**Solution:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Check browser console for JavaScript errors (F12)
3. Verify JavaScript is enabled

### Issue: "Complainant Information" not showing in emails
**Solution:**
1. Clear email template cache (if any)
2. Check that template variables are being replaced correctly
3. Verify email_address field is not being used instead of complaint_email

## Additional Notes

- The new fields store information provided by the complainant on the review page
- This is separate from the form data and allows for a secondary contact method
- Useful if the form contains someone else's information but the complaint is filed by another person
- All email templates now display this information for better traceability

---

**Deployment Date:** [Insert Date]
**Deployed By:** [Insert Name]
**Status:** [Pending/In Progress/Complete]
