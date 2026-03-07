# Supplier and Purchase Management System

## Overview
A comprehensive supplier and purchase management system has been implemented with payment tracking, stock batch management, document attachments, and barcode scanning capabilities.

## Features Implemented

### 1. Supplier Management
- **CRUD Operations**: Create, read, update, and delete suppliers
- **Supplier Information**: Name, contact person, email, phone, address, credit limit
- **Status Management**: Active/Inactive supplier status
- **Search & Filter**: Search by name, contact, email, phone; filter by status

### 2. Supplier Payments
- **Payment Recording**: Record partial or full payments to suppliers
- **Payment Methods**: Cash, Bank Transfer, Cheque, Credit Card
- **Payment Tracking**: 
  - Track payments made to suppliers
  - Link payments to specific purchase orders
  - View payment history
  - Calculate total paid vs pending amounts
- **Payment Management**: 
  - View all payments for a supplier
  - Delete payments (with automatic balance updates)
  - Payment reference numbers and notes

### 3. Purchase Orders Enhancement
- **Purchase Conditions**: Add delivery time and purchase conditions
- **Document Attachments**: 
  - Upload receipts for purchases
  - Upload invoices
  - Upload delivery notes
  - Upload proof of delivery documents
- **Payment Tracking**: Track paid amount and remaining amount per purchase order
- **Status Management**: Pending, Approved, Received, Cancelled

### 4. Stock Batch Management
- **Batch Creation**: Create stock batches when receiving purchases
- **Batch Information**:
  - Batch number (auto-generated)
  - Quantity received
  - Unit cost
  - Manufacture date (optional)
  - Expiry date (optional)
  - Received date
- **Batch Tracking**:
  - View all stock batches
  - Filter by product
  - Filter by expiring soon (30 days)
  - Track batch history per product
- **Expiry Management**:
  - Identify expired batches
  - Identify batches expiring soon
  - Visual indicators for expiry status

### 5. Product Receiving
- **Receive Purchase Orders**: 
  - Receive products from purchase orders
  - Create stock batches automatically
  - Update product stock quantities
  - Record stock movements
- **Batch Assignment**: Assign batch numbers, expiry dates when receiving

### 6. Barcode Scanning
- **Barcode Search**: Search products by barcode
- **Quick Product Addition**: Add products to purchase orders using barcode scanner
- **Product Lookup**: Find existing products quickly

### 7. Document Management
- **Purchase Documents**: Upload receipts, invoices, delivery notes for purchases
- **Delivery Documents**: Upload delivery notes and proof of delivery
- **Expense Documents**: Upload receipts for expenses
- **File Support**: PDF, JPG, JPEG, PNG, DOC, DOCX (up to 10MB)

## Database Structure

### New Tables
1. **supplier_payments**: Tracks payments made to suppliers
2. **stock_batches**: Tracks stock batches with expiry dates
3. **purchase_documents**: Stores purchase-related documents
4. **expense_documents**: Stores expense-related documents
5. **delivery_documents**: Stores delivery-related documents

### Updated Tables
- **purchase_orders**: Added `delivery_time_days`, `purchase_conditions`, `paid_amount`, `remaining_amount`

## Models Created

1. **SupplierPayment**: Manages supplier payments
2. **StockBatch**: Manages stock batches
3. **PurchaseDocument**: Manages purchase documents
4. **ExpenseDocument**: Manages expense documents
5. **DeliveryDocument**: Manages delivery documents

## Controllers

### SupplierController
- `index()`: List all suppliers
- `store()`: Create new supplier
- `update()`: Update supplier
- `destroy()`: Delete supplier
- `show()`: View supplier details
- `payments()`: View supplier payments
- `storePayment()`: Record payment
- `deletePayment()`: Delete payment

### Enhanced POSController
- `receivePurchaseOrder()`: Receive purchase order with batches
- `uploadPurchaseDocument()`: Upload purchase documents
- `uploadDeliveryDocument()`: Upload delivery documents
- `uploadExpenseDocument()`: Upload expense documents
- `searchProductByBarcode()`: Search product by barcode
- `stockBatches()`: View all stock batches
- `productStockBatches()`: View batches for a specific product

## Frontend Components

### Suppliers
- **Index.vue**: List all suppliers with search and filter
- **Show.vue**: View supplier details with financial summary
- **Payments.vue**: Manage supplier payments

### Purchases
- **Index.vue**: Enhanced purchase orders page with:
  - Barcode scanning
  - Receipt upload
  - Purchase order creation
  - Purchase order receiving
  - Document management

### Inventory
- **StockBatches.vue**: View all stock batches with expiry tracking

## Routes

All routes are prefixed with `/pos` and named with `pos.` prefix:

### Suppliers
- `GET /pos/suppliers` - List suppliers
- `POST /pos/suppliers` - Create supplier
- `PUT /pos/suppliers/{supplier}` - Update supplier
- `DELETE /pos/suppliers/{supplier}` - Delete supplier
- `GET /pos/suppliers/{supplier}` - View supplier
- `GET /pos/suppliers/{supplier}/payments` - View payments
- `POST /pos/suppliers/{supplier}/payments` - Record payment
- `DELETE /pos/supplier-payments/{payment}` - Delete payment

### Purchases
- `GET /pos/purchases` - List purchase orders
- `POST /pos/purchase-orders` - Create purchase order
- `POST /pos/purchase-orders/{purchaseOrder}/receive` - Receive purchase order
- `POST /pos/purchase-orders/{purchaseOrder}/upload-document` - Upload purchase document
- `POST /pos/purchase-orders/{purchaseOrder}/upload-delivery-document` - Upload delivery document

### Products & Batches
- `GET /pos/products/search/barcode` - Search product by barcode
- `GET /pos/products/{product}/stock-batches` - View product batches
- `GET /pos/stock-batches` - View all stock batches

### Expenses
- `POST /pos/expenses/{expense}/upload-document` - Upload expense document

## Permissions Required

- `manage_suppliers`: Manage suppliers and payments
- `manage_purchases`: Manage purchase orders
- `manage_inventory`: View stock batches
- `manage_expenses`: Upload expense documents

## Usage

### Creating a Purchase Order
1. Navigate to POS > Purchases
2. Click "Create Purchase Order"
3. Select supplier
4. Add products using barcode scanner or browse
5. Set delivery time and purchase conditions
6. Create the purchase order

### Receiving a Purchase Order
1. View the purchase order
2. Click "Receive"
3. Enter quantities received for each item
4. Add batch numbers and expiry dates (optional)
5. Submit to create stock batches and update inventory

### Recording Supplier Payment
1. Navigate to Suppliers
2. Click on a supplier
3. Go to Payments tab
4. Click "Add Payment"
5. Select purchase order (optional)
6. Enter payment details
7. Record payment

### Viewing Stock Batches
1. Navigate to POS > Inventory > Stock Batches
2. View all batches with expiry information
3. Filter by product or expiring soon

## File Storage

Documents are stored in:
- `storage/app/public/purchase-documents/`
- `storage/app/public/delivery-documents/`
- `storage/app/public/expense-documents/`

Make sure to run `php artisan storage:link` to create the symbolic link.

## Migration

Run the migration to create the new tables:
```bash
php artisan migrate
```

## Notes

- Stock batches automatically update product stock quantities
- Payments automatically update purchase order payment status
- Expiry tracking helps identify products expiring soon
- Barcode scanning speeds up product entry
- All documents are stored securely with proper access control
