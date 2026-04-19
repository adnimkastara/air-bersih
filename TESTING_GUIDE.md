# Air Bersih MVP - Testing Guide

## Overview
Panduan lengkap untuk test semua fitur MVP Air Bersih secara end-to-end.

---

## 🧪 Test 1: Authentication & Login

### Objective
Verify login dan register functionality dengan role assignment yang benar.

### Prerequisites
- Application running at `http://localhost/air-bersih`
- Database seeded with test users

### Test Steps

#### 1.1 Test Login with Petugas Account
1. Navigate to `http://localhost/air-bersih/login`
2. Enter credentials:
   - Email: `petugas@airbersih.com`
   - Password: `Petugas123!`
3. Click "Login"

**Expected Result**: 
- ✅ Login successful
- ✅ Redirected to dashboard
- ✅ User name/email shown in header
- ✅ Dashboard displays "Petugas Lapangan" role

#### 1.2 Test Admin Login
1. Navigate to `http://localhost/air-bersih/login`
2. Enter credentials:
   - Email: `root@airbersih.com`
   - Password: `Admin1234!`
3. Click "Login"

**Expected Result**:
- ✅ Login successful
- ✅ Dashboard shows "Admin Management" button (admin-only feature)
- ✅ User has full access to all menus

#### 1.3 Test Logout
1. Click "Logout" button in navigation
2. Redirected to login page

**Expected Result**:
- ✅ Session cleared
- ✅ Cannot access protected routes
- ✅ Redirected to `/login` when accessing `/dashboard`

---

## 🧪 Test 2: Master Data - Kecamatan (Read-Only)

### Objective
Verify kecamatan master data is displayed as read-only (no CRUD operations).

### Prerequisites
- Logged in as any user (dashboard access)
- Kecamatan seeded: "Kecamatan Utama"

### Test Steps

#### 2.1 View Kecamatan List
1. Click "Master Kecamatan" button on dashboard OR
2. Navigate to `http://localhost/air-bersih/kecamatan`

**Expected Result**:
- ✅ Table displayed with kecamatan data
- ✅ Columns: ID, Name
- ✅ Shows "Kecamatan Utama"
- ✅ NO "Add" button
- ✅ NO "Edit/Delete" action buttons
- ✅ Read-only display

#### 2.2 Verify No Create Route
1. Try to access `http://localhost/air-bersih/kecamatan/create`

**Expected Result**:
- ✅ Route not found (404) OR
- ✅ No create button visible anywhere

---

## 🧪 Test 3: Master Data - Desa (Full CRUD)

### Objective
Verify Desa management with create, read, update, delete operations.

### Prerequisites
- Logged in with admin or kecamatan admin
- Kecamatan "Kecamatan Utama" exists
- Desa seeded: "Desa Satu"

### Test Steps

#### 3.1 View Desa List
1. Click "Master Desa" on dashboard OR
2. Navigate to `http://localhost/air-bersih/desa`

**Expected Result**:
- ✅ Table with existing desa displayed
- ✅ Shows "Desa Satu" under "Kecamatan Utama"
- ✅ "Add Desa" button visible

#### 3.2 Create New Desa
1. Click "Add Desa" button
2. Fill form:
   - Kecamatan: Select "Kecamatan Utama"
   - Desa Name: "Desa Dua"
3. Click "Save"

**Expected Result**:
- ✅ Validation passes
- ✅ New desa created
- ✅ Redirected to desa list
- ✅ "Desa Dua" appears in table

#### 3.3 Edit Desa
1. Click "Edit" on "Desa Dua"
2. Change name to "Desa Dua Updated"
3. Click "Update"

**Expected Result**:
- ✅ Desa name updated
- ✅ Returns to list with updated data

#### 3.4 Delete Desa
1. Click "Delete" on "Desa Dua Updated"
2. Confirm deletion

**Expected Result**:
- ✅ Desa deleted
- ✅ No longer appears in list

---

## 🧪 Test 4: Customer Management (Pelanggan)

### Objective
Verify customer CRUD with all fields and relationships.

### Prerequisites
- Logged in with admin
- Desa exists
- Field officer exists: petugas@airbersih.com

### Test Steps

#### 4.1 View Pelanggan List
1. Click "Data Pelanggan" on dashboard OR
2. Navigate to `http://localhost/air-bersih/pelanggan`

**Expected Result**:
- ✅ Table shows existing customer
- ✅ Shows "Pelanggan Satu" with status "aktif"
- ✅ "Add Pelanggan" button visible

#### 4.2 Create New Pelanggan
1. Click "Add Pelanggan"
2. Fill form:
   - Name: "Pelanggan Dua"
   - Email: "pelanggan2@example.com"
   - Phone: "08987654321"
   - Address: "Jalan Test No. 2"
   - Kecamatan: "Kecamatan Utama"
   - Desa: "Desa Satu"
   - Petugas: "petugas@airbersih.com"
   - Status: "aktif"
   - Latitude: "-6.2000" (optional)
   - Longitude: "106.8000" (optional)
3. Click "Save"

**Expected Result**:
- ✅ All validations pass
- ✅ Customer created
- ✅ Returns to list
- ✅ "Pelanggan Dua" appears in table

#### 4.3 Edit Pelanggan
1. Click "Edit" on "Pelanggan Dua"
2. Change status to "nonaktif"
3. Click "Update"

**Expected Result**:
- ✅ Status updated to "nonaktif"
- ✅ Data persists in list

#### 4.4 Delete Pelanggan
1. Click "Delete" on "Pelanggan Dua"
2. Confirm

**Expected Result**:
- ✅ Customer deleted
- ✅ Returns to list

---

## 🧪 Test 5: Meter Recording (Pencatatan Meter)

### Objective
Verify meter recording with dual-field structure and validation.

### Prerequisites
- Logged in as petugas (petugas@airbersih.com)
- Customer exists: "Pelanggan Satu"
- Sample meter record exists: Previous=100, Current=150

### Test Steps

#### 5.1 View Meter Records
1. Click "Pencatatan Meter" on dashboard OR
2. Navigate to `http://localhost/air-bersih/meter-records`

**Expected Result**:
- ✅ Table displays existing meter record
- ✅ Shows columns: Pelanggan | Meter Bulan Lalu | Meter Bulan Ini | Konsumsi | Petugas | Tanggal
- ✅ Sample data shows: Pelanggan Satu | 100 | 150 | 50 | petugas@airbersih.com | 2026-04-19
- ✅ "Record Meter" button visible

#### 5.2 Record Valid Meter Reading
1. Click "Record Meter"
2. Fill form:
   - Pelanggan: "Pelanggan Satu"
   - Meter Bulan Lalu: "150" (previous month = current from previous record)
   - Meter Bulan Ini: "200" (current reading)
   - Tanggal: "2026-05-19" (today's date)
   - Petugas: Auto-filled with logged-in user
   - Catatan: "Meter recorded successfully"
3. Click "Save"

**Expected Result**:
- ✅ Validation passes
- ✅ Meter record created
- ✅ New row appears in table
- ✅ Konsumsi calculated correctly: 200 - 150 = 50

#### 5.3 Test Anomaly Detection (Current < Previous)
1. Click "Record Meter"
2. Fill form:
   - Pelanggan: "Pelanggan Satu"
   - Meter Bulan Lalu: "200"
   - Meter Bulan Ini: "150" (less than previous - invalid)
3. Click "Save"

**Expected Result**:
- ✅ Validation FAILS
- ✅ Error message in Indonesian: "Meter bulan ini tidak boleh kurang dari meter bulan lalu"
- ✅ Form not submitted

#### 5.4 Test Zero/Negative Consumption
1. Click "Record Meter"
2. Fill form:
   - Meter Bulan Lalu: "200"
   - Meter Bulan Ini: "200" (zero consumption)
3. Click "Save"

**Expected Result**:
- ✅ Record can be saved (consumption can be 0)
- ✅ Meter record created with konsumsi = 0

---

## 🧪 Test 6: Invoice Generation (Tagihan)

### Objective
Verify invoice auto-generation from meter records with consumption-based calculation.

### Prerequisites
- Logged in as admin
- Meter records exist with consumption > 0
- Sample: Previous=100, Current=150 (Consumption=50)

### Test Steps

#### 6.1 View Invoice List (Before Generation)
1. Click "Tagihan" on dashboard OR
2. Navigate to `http://localhost/air-bersih/tagihan`

**Expected Result**:
- ✅ Table may be empty or show existing invoices
- ✅ Columns: Pelanggan | Periode | Jumlah | Status | Jatuh Tempo | Aksi
- ✅ "Generate Tagihan" button visible

#### 6.2 Generate Invoices
1. Click "Generate Tagihan" button
2. System processes all meter records

**Expected Result**:
- ✅ Auto-generation completes
- ✅ Success message displayed
- ✅ New invoices appear in list with status "draft"
- ✅ Amount calculated: consumption × 1,000
  - Example: 50 consumption × 1,000 = 50,000

#### 6.3 Verify Generated Invoice Details
1. Click on generated invoice row

**Expected Result**:
- ✅ Shows invoice details:
  - Status: "draft"
  - Amount: 50,000 (for 50 consumption)
  - Period: Month of meter recording
  - Due Date: End of that month
  - Customer: "Pelanggan Satu"

#### 6.4 Publish Invoice (Draft → Terbit)
1. On invoice with status "draft", click "Publish" button

**Expected Result**:
- ✅ Status changes: "draft" → "terbit"
- ✅ Button no longer available for this invoice
- ✅ Color badge updates

#### 6.5 Verify Invoice Won't Generate Twice
1. Click "Generate Tagihan" again
2. System processes meter records

**Expected Result**:
- ✅ No duplicate invoices created
- ✅ System skips meter records with existing tagihans
- ✅ Message indicates "No new invoices generated"

---

## 🧪 Test 7: Payment Recording (Pembayaran)

### Objective
Verify payment recording and automatic invoice status updates.

### Prerequisites
- Logged in with admin or petugas
- At least one published invoice exists (status "terbit")
- Invoice amount is known

### Test Steps

#### 7.1 View Payment List
1. Click "Pembayaran" on dashboard OR
2. Navigate to `http://localhost/air-bersih/pembayaran`

**Expected Result**:
- ✅ Table shows existing payments (if any)
- ✅ Columns: Tagihan | Pelanggan | Jumlah | Tanggal | Petugas | Catatan
- ✅ "Record Pembayaran" button visible

#### 7.2 Record Full Payment
1. Click "Record Pembayaran"
2. Fill form:
   - Tagihan: Select invoice with amount 50,000
   - Amount: "50000" (full payment)
   - Date: "2026-04-20"
   - Petugas: Auto-filled with current user
   - Notes: "Full payment"
3. Click "Save"

**Expected Result**:
- ✅ Validation passes
- ✅ Payment created
- ✅ Redirected to payment list
- ✅ New payment appears

#### 7.3 Verify Invoice Status Updated to "Lunas" (Paid)
1. Navigate back to Tagihan list
2. Find the invoice that was just paid

**Expected Result**:
- ✅ Invoice status: "lunas" (paid in full)
- ✅ Status badge shows green color
- ✅ Amount matches payment

#### 7.4 Record Partial Payment (Creates Menunggak)
1. Create another meter record with consumption 100 (amount: 100,000)
2. Generate invoice
3. Publish invoice
4. Record payment:
   - Amount: "50000" (partial, only 50%)
   - Date: After due_date (e.g., due 2026-04-30, paid 2026-05-05)
5. Click "Save"

**Expected Result**:
- ✅ Payment created
- ✅ Invoice status: "menunggak" (overdue unpaid)
- ✅ Status badge shows red color
- ✅ Reason: paid_date > due_date

#### 7.5 View Receipt
1. On payment row, click "View Receipt" link
2. Page displays receipt details

**Expected Result**:
- ✅ Receipt page shows:
  - Invoice details (amount, period, customer)
  - Payment details (amount paid, date)
  - Petugas name
  - Print button functional

#### 7.6 Test Print Receipt
1. Click "Print" button on receipt page
2. Browser print dialog appears

**Expected Result**:
- ✅ Receipt formatted for printing
- ✅ All relevant information included
- ✅ Can be printed to PDF or paper

---

## 🧪 Test 8: Dashboard KPIs

### Objective
Verify dashboard displays correct KPI values.

### Prerequisites
- Logged in with admin
- Multiple records created: customers, invoices, payments

### Test Steps

#### 8.1 Check Dashboard Stats
1. Click "Dashboard" OR navigate to `http://localhost/air-bersih/dashboard`

**Expected Result**:
- ✅ 5 stat cards displayed:

| Card | Expected Value | Calculation |
|------|---|---|
| Total Pelanggan | ≥ 1 | COUNT from pelanggans table |
| Total Tagihan | ≥ 1 | COUNT from tagihans table |
| Total Pembayaran | ≥ 1 | COUNT from pembayarans table |
| Total Tunggakan | ≥ 0 | COUNT where status='menunggak' |
| Jumlah Gangguan | ≥ 0 | COUNT from activity_logs |

#### 8.2 Verify KPI Updates
1. Create new customer in Pelanggan
2. Return to Dashboard
3. "Total Pelanggan" should increment

**Expected Result**:
- ✅ KPI values update in real-time
- ✅ Database queries are accurate

---

## 🧪 Test 9: Activity Logging

### Objective
Verify all CRUD operations are logged.

### Prerequisites
- Logged in with admin
- Perform various CRUD operations

### Test Steps

#### 9.1 Create Customer and Check Activity Log
1. Navigate to Pelanggan and create new customer
2. Execute query:
   ```sql
   SELECT * FROM activity_logs 
   ORDER BY created_at DESC 
   LIMIT 5;
   ```

**Expected Result**:
- ✅ New log entry appears
- ✅ Fields populated:
  - user_id: Logged-in user
  - action: "created" or "pelanggan.created"
  - subject_type: "Pelanggan"
  - subject_id: New customer ID
  - description: Descriptive message

#### 9.2 Update and Delete Operations
1. Perform update on existing desa
2. Check activity_logs

**Expected Result**:
- ✅ "updated" action logged
- ✅ Update details recorded

#### 9.3 Dashboard Shows Activity Count
1. Check dashboard "Jumlah Gangguan" KPI
2. Verify count matches activity_logs

**Expected Result**:
- ✅ Count reflects recent activity

---

## 🧪 Test 10: Role-Based Access

### Objective
Verify different user roles can access appropriate features.

### Prerequisites
- Multiple test users with different roles

### Test Steps

#### 10.1 Petugas Lapangan Access
1. Login as petugas@airbersih.com
2. Dashboard accessible: YES
3. Pencatatan Meter menu: YES
4. Can view other features: YES (but should be restricted)

**Current Status**: All authenticated users can access all pages
**Future Enhancement**: Implement middleware to restrict by role

#### 10.2 Admin Access
1. Login as root@airbersih.com
2. Dashboard accessible: YES
3. Admin Management button visible: YES
4. All menus accessible: YES

---

## 📊 Complete End-to-End Flow Summary

```
1. Login (petugas@airbersih.com)
   ↓
2. Record Meter (100 → 150, consumption=50)
   ↓
3. Login (admin)
   ↓
4. Generate Tagihan (50 × 1,000 = 50,000)
   ↓
5. Publish Tagihan (draft → terbit)
   ↓
6. Record Payment (50,000)
   ↓
7. Verify Status (terbit → lunas)
   ↓
8. View Receipt
   ↓
9. Check Dashboard (all KPIs updated)
   ↓
10. View Activity Logs (all actions logged)
```

---

## 🐛 Known Issues & Limitations

1. **No Role-Based Route Protection**: All authenticated users can access all pages
   - Workaround: Implement middleware for role checks
   
2. **Session Driver**: Uses file-based sessions (not database)
   - Works fine for MVP, scale to database sessions later

3. **No Pagination**: Lists show all records
   - Performance: Add pagination for large datasets

4. **Limited Error Handling**: Basic validation messages
   - Enhancement: Add detailed error messages

---

## ✅ Test Checklist

- [ ] Authentication (login/logout)
- [ ] Kecamatan (read-only)
- [ ] Desa (CRUD)
- [ ] Pelanggan (CRUD)
- [ ] Meter Recording (with validation)
- [ ] Invoice Generation (consumption-based)
- [ ] Payment Recording (status update)
- [ ] Receipt Printing
- [ ] Dashboard KPIs
- [ ] Activity Logging
- [ ] Role-based access (basic)

---

## 📝 Quick Test Data Reference

**Test Accounts**:
```
root@airbersih.com / Admin1234! (Root)
kecamatan@airbersih.com / Admin1234! (Kecamatan Admin)
desa@airbersih.com / Admin1234! (Desa Admin)
petugas@airbersih.com / Petugas123! (Field Officer)
```

**Seeded Data**:
- Kecamatan: "Kecamatan Utama"
- Desa: "Desa Satu"
- Pelanggan: "Pelanggan Satu" (aktif)
- Meter: 100 → 150 (consumption: 50)

---

**Last Updated**: April 2026
**Version**: MVP 1.0
