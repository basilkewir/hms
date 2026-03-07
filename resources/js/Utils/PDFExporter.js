/**
 * PDF Exporter Utility
 * Provides clean, professional PDF export designs for quotes and invoices
 * Separate from web UI design for professional document output
 */

export class PDFExporter {
    /**
     * Generate professional quote PDF HTML
     * @param {Object} quoteData - Quote object with customer, items, totals
     * @returns {String} HTML string ready for PDF export
     */
    static generateQuotePDF(quoteData) {
        const {
            quote_number = 'N/A',
            customer_name = 'N/A',
            customer_email = '',
            customer_phone = '',
            valid_until = '',
            items = [],
            subtotal = 0,
            tax_amount = 0,
            total_amount = 0,
            tax_percentage = 0,
            notes = '',
            created_at = new Date().toLocaleDateString()
        } = quoteData

        const itemsHTML = items
            .filter(item => item.description && item.quantity && item.unit_price)
            .map((item, index) => `
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">${index + 1}</td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">${item.description}</td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">${item.quantity}</td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">$${parseFloat(item.unit_price).toFixed(2)}</td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">$${(item.quantity * item.unit_price).toFixed(2)}</td>
            </tr>
        `).join('')

        const formattedValidUntil = valid_until
            ? new Date(valid_until).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
            : 'N/A'

        const formattedCreatedAt = new Date(created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })

        return `
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Quote #${quote_number}</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        background: white;
                        padding: 40px 20px;
                    }

                    .container {
                        max-width: 800px;
                        margin: 0 auto;
                        background: white;
                    }

                    /* Header */
                    .header {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        border-bottom: 3px solid #1a5490;
                        padding-bottom: 20px;
                        margin-bottom: 30px;
                    }

                    .header-left h1 {
                        font-size: 32px;
                        color: #1a5490;
                        margin-bottom: 5px;
                    }

                    .header-left p {
                        font-size: 12px;
                        color: #666;
                    }

                    .header-right {
                        text-align: right;
                    }

                    .quote-number {
                        font-size: 18px;
                        font-weight: bold;
                        color: #1a5490;
                        margin-bottom: 5px;
                    }

                    .quote-date {
                        font-size: 12px;
                        color: #666;
                    }

                    /* Two Column Layout */
                    .content {
                        display: flex;
                        gap: 30px;
                        margin-bottom: 30px;
                    }

                    .column {
                        flex: 1;
                    }

                    /* Customer Info Section */
                    .section {
                        margin-bottom: 20px;
                    }

                    .section-title {
                        font-size: 12px;
                        font-weight: bold;
                        color: #1a5490;
                        text-transform: uppercase;
                        margin-bottom: 10px;
                        border-bottom: 1px solid #e0e0e0;
                        padding-bottom: 5px;
                    }

                    .info-row {
                        display: flex;
                        margin-bottom: 8px;
                        font-size: 11px;
                    }

                    .info-label {
                        font-weight: bold;
                        width: 80px;
                        color: #666;
                    }

                    .info-value {
                        flex: 1;
                        color: #333;
                        word-break: break-word;
                    }

                    /* Items Table */
                    .items-section {
                        margin: 30px 0;
                    }

                    .items-section .section-title {
                        margin-bottom: 15px;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }

                    thead tr {
                        background-color: #f5f5f5;
                    }

                    th {
                        padding: 10px;
                        text-align: left;
                        font-weight: bold;
                        font-size: 11px;
                        color: #333;
                        border-bottom: 2px solid #1a5490;
                    }

                    th:nth-child(1) { width: 5%; }
                    th:nth-child(2) { width: 40%; }
                    th:nth-child(3) { width: 15%; text-align: center; }
                    th:nth-child(4) { width: 20%; text-align: right; }
                    th:nth-child(5) { width: 20%; text-align: right; }

                    td {
                        font-size: 11px;
                        color: #333;
                    }

                    /* Totals Section */
                    .totals-section {
                        display: flex;
                        justify-content: flex-end;
                        margin-top: 20px;
                        margin-bottom: 30px;
                    }

                    .totals {
                        width: 250px;
                    }

                    .total-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 8px 0;
                        font-size: 12px;
                        border-bottom: 1px solid #e0e0e0;
                    }

                    .total-row.subtotal .label {
                        color: #666;
                    }

                    .total-row.tax .label {
                        color: #666;
                    }

                    .total-row.final {
                        border: none;
                        border-top: 2px solid #1a5490;
                        border-bottom: 2px solid #1a5490;
                        font-weight: bold;
                        font-size: 14px;
                        color: #1a5490;
                        padding: 12px 0;
                        margin-top: 10px;
                    }

                    .total-row .amount {
                        text-align: right;
                    }

                    /* Notes Section */
                    .notes-section {
                        background-color: #f9f9f9;
                        padding: 15px;
                        border-left: 3px solid #1a5490;
                        margin-bottom: 30px;
                    }

                    .notes-section .section-title {
                        margin-bottom: 10px;
                        border: none;
                    }

                    .notes-section p {
                        font-size: 11px;
                        color: #333;
                        line-height: 1.6;
                    }

                    /* Footer */
                    .footer {
                        border-top: 1px solid #e0e0e0;
                        padding-top: 15px;
                        font-size: 10px;
                        color: #999;
                        text-align: center;
                    }

                    /* Print Styles */
                    @media print {
                        body {
                            padding: 0;
                        }
                        .container {
                            max-width: 100%;
                        }
                    }

                    /* Page Break Handling */
                    @page {
                        margin: 20mm;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <!-- Header -->
                    <div class="header">
                        <div class="header-left">
                            <h1>QUOTE</h1>
                            <p>Professional Quote Document</p>
                        </div>
                        <div class="header-right">
                            <div class="quote-number">#${quote_number}</div>
                            <div class="quote-date">Date: ${formattedCreatedAt}</div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="content">
                        <!-- Left Column: From/To -->
                        <div class="column">
                            <div class="section">
                                <div class="section-title">Bill To</div>
                                <div class="info-row">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value">${customer_name}</span>
                                </div>
                                ${customer_email ? `
                                <div class="info-row">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value">${customer_email}</span>
                                </div>
                                ` : ''}
                                ${customer_phone ? `
                                <div class="info-row">
                                    <span class="info-label">Phone:</span>
                                    <span class="info-value">${customer_phone}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Right Column: Important Dates -->
                        <div class="column">
                            <div class="section">
                                <div class="section-title">Quote Details</div>
                                <div class="info-row">
                                    <span class="info-label">Valid Until:</span>
                                    <span class="info-value">${formattedValidUntil}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="items-section">
                        <div class="section-title">Line Items</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHTML}
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="totals-section">
                        <div class="totals">
                            <div class="total-row subtotal">
                                <span class="label">Subtotal:</span>
                                <span class="amount">$${parseFloat(subtotal).toFixed(2)}</span>
                            </div>
                            ${tax_percentage > 0 ? `
                            <div class="total-row tax">
                                <span class="label">Tax (${tax_percentage}%):</span>
                                <span class="amount">$${parseFloat(tax_amount).toFixed(2)}</span>
                            </div>
                            ` : ''}
                            <div class="total-row final">
                                <span class="label">TOTAL:</span>
                                <span class="amount">$${parseFloat(total_amount).toFixed(2)}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    ${notes ? `
                    <div class="notes-section">
                        <div class="section-title">Notes</div>
                        <p>${notes}</p>
                    </div>
                    ` : ''}

                    <!-- Footer -->
                    <div class="footer">
                        <p>This is a professional quote document. Please contact us if you have any questions.</p>
                    </div>
                </div>
            </body>
            </html>
        `
    }

    /**
     * Generate professional invoice PDF HTML
     * @param {Object} invoiceData - Invoice object with customer, items, totals
     * @returns {String} HTML string ready for PDF export
     */
    static generateInvoicePDF(invoiceData) {
        const {
            invoice_number = 'N/A',
            customer_name = 'N/A',
            customer_email = '',
            customer_phone = '',
            due_date = '',
            items = [],
            subtotal = 0,
            tax_amount = 0,
            total_amount = 0,
            tax_percentage = 0,
            balance_due = 0,
            status = 'open',
            notes = '',
            created_at = new Date().toLocaleDateString()
        } = invoiceData

        const itemsHTML = items
            .filter(item => item.description && item.quantity && item.unit_price)
            .map((item, index) => `
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">${index + 1}</td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;">${item.description}</td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">${item.quantity}</td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">$${parseFloat(item.unit_price).toFixed(2)}</td>
                <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">$${(item.quantity * item.unit_price).toFixed(2)}</td>
            </tr>
        `).join('')

        const formattedDueDate = due_date
            ? new Date(due_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
            : 'N/A'

        const formattedCreatedAt = new Date(created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })

        const statusColor = status === 'paid' ? '#10b981' : (status === 'overdue' ? '#ef4444' : '#3b82f6')

        return `
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Invoice #${invoice_number}</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        line-height: 1.6;
                        color: #333;
                        background: white;
                        padding: 40px 20px;
                    }

                    .container {
                        max-width: 800px;
                        margin: 0 auto;
                        background: white;
                    }

                    /* Header */
                    .header {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        border-bottom: 3px solid #16a34a;
                        padding-bottom: 20px;
                        margin-bottom: 30px;
                    }

                    .header-left h1 {
                        font-size: 32px;
                        color: #16a34a;
                        margin-bottom: 5px;
                    }

                    .header-left p {
                        font-size: 12px;
                        color: #666;
                    }

                    .header-right {
                        text-align: right;
                    }

                    .invoice-number {
                        font-size: 18px;
                        font-weight: bold;
                        color: #16a34a;
                        margin-bottom: 5px;
                    }

                    .invoice-date {
                        font-size: 12px;
                        color: #666;
                    }

                    .status-badge {
                        display: inline-block;
                        padding: 6px 12px;
                        border-radius: 4px;
                        font-size: 11px;
                        font-weight: bold;
                        color: white;
                        margin-top: 5px;
                        background-color: ${statusColor};
                    }

                    /* Two Column Layout */
                    .content {
                        display: flex;
                        gap: 30px;
                        margin-bottom: 30px;
                    }

                    .column {
                        flex: 1;
                    }

                    /* Customer Info Section */
                    .section {
                        margin-bottom: 20px;
                    }

                    .section-title {
                        font-size: 12px;
                        font-weight: bold;
                        color: #16a34a;
                        text-transform: uppercase;
                        margin-bottom: 10px;
                        border-bottom: 1px solid #e0e0e0;
                        padding-bottom: 5px;
                    }

                    .info-row {
                        display: flex;
                        margin-bottom: 8px;
                        font-size: 11px;
                    }

                    .info-label {
                        font-weight: bold;
                        width: 80px;
                        color: #666;
                    }

                    .info-value {
                        flex: 1;
                        color: #333;
                        word-break: break-word;
                    }

                    /* Items Table */
                    .items-section {
                        margin: 30px 0;
                    }

                    .items-section .section-title {
                        margin-bottom: 15px;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }

                    thead tr {
                        background-color: #f5f5f5;
                    }

                    th {
                        padding: 10px;
                        text-align: left;
                        font-weight: bold;
                        font-size: 11px;
                        color: #333;
                        border-bottom: 2px solid #16a34a;
                    }

                    th:nth-child(1) { width: 5%; }
                    th:nth-child(2) { width: 40%; }
                    th:nth-child(3) { width: 15%; text-align: center; }
                    th:nth-child(4) { width: 20%; text-align: right; }
                    th:nth-child(5) { width: 20%; text-align: right; }

                    td {
                        font-size: 11px;
                        color: #333;
                    }

                    /* Totals Section */
                    .totals-section {
                        display: flex;
                        justify-content: flex-end;
                        margin-top: 20px;
                        margin-bottom: 30px;
                    }

                    .totals {
                        width: 250px;
                    }

                    .total-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 8px 0;
                        font-size: 12px;
                        border-bottom: 1px solid #e0e0e0;
                    }

                    .total-row.subtotal .label {
                        color: #666;
                    }

                    .total-row.tax .label {
                        color: #666;
                    }

                    .total-row.final {
                        border: none;
                        border-top: 2px solid #16a34a;
                        border-bottom: 2px solid #16a34a;
                        font-weight: bold;
                        font-size: 14px;
                        color: #16a34a;
                        padding: 12px 0;
                        margin-top: 10px;
                    }

                    .total-row .amount {
                        text-align: right;
                    }

                    .balance-due {
                        display: flex;
                        justify-content: space-between;
                        padding: 10px 0;
                        font-size: 12px;
                        margin-top: 10px;
                        color: #ef4444;
                        font-weight: bold;
                    }

                    /* Notes Section */
                    .notes-section {
                        background-color: #f9f9f9;
                        padding: 15px;
                        border-left: 3px solid #16a34a;
                        margin-bottom: 30px;
                    }

                    .notes-section .section-title {
                        margin-bottom: 10px;
                        border: none;
                    }

                    .notes-section p {
                        font-size: 11px;
                        color: #333;
                        line-height: 1.6;
                    }

                    /* Footer */
                    .footer {
                        border-top: 1px solid #e0e0e0;
                        padding-top: 15px;
                        font-size: 10px;
                        color: #999;
                        text-align: center;
                    }

                    /* Print Styles */
                    @media print {
                        body {
                            padding: 0;
                        }
                        .container {
                            max-width: 100%;
                        }
                    }

                    /* Page Break Handling */
                    @page {
                        margin: 20mm;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <!-- Header -->
                    <div class="header">
                        <div class="header-left">
                            <h1>INVOICE</h1>
                            <p>Professional Invoice Document</p>
                        </div>
                        <div class="header-right">
                            <div class="invoice-number">#${invoice_number}</div>
                            <div class="invoice-date">Date: ${formattedCreatedAt}</div>
                            <div class="status-badge">${status.toUpperCase()}</div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="content">
                        <!-- Left Column: From/To -->
                        <div class="column">
                            <div class="section">
                                <div class="section-title">Bill To</div>
                                <div class="info-row">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value">${customer_name}</span>
                                </div>
                                ${customer_email ? `
                                <div class="info-row">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value">${customer_email}</span>
                                </div>
                                ` : ''}
                                ${customer_phone ? `
                                <div class="info-row">
                                    <span class="info-label">Phone:</span>
                                    <span class="info-value">${customer_phone}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Right Column: Important Dates -->
                        <div class="column">
                            <div class="section">
                                <div class="section-title">Invoice Details</div>
                                <div class="info-row">
                                    <span class="info-label">Due Date:</span>
                                    <span class="info-value">${formattedDueDate}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="items-section">
                        <div class="section-title">Line Items</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsHTML}
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="totals-section">
                        <div class="totals">
                            <div class="total-row subtotal">
                                <span class="label">Subtotal:</span>
                                <span class="amount">$${parseFloat(subtotal).toFixed(2)}</span>
                            </div>
                            ${tax_percentage > 0 ? `
                            <div class="total-row tax">
                                <span class="label">Tax (${tax_percentage}%):</span>
                                <span class="amount">$${parseFloat(tax_amount).toFixed(2)}</span>
                            </div>
                            ` : ''}
                            <div class="total-row final">
                                <span class="label">TOTAL:</span>
                                <span class="amount">$${parseFloat(total_amount).toFixed(2)}</span>
                            </div>
                            ${balance_due > 0 ? `
                            <div class="balance-due">
                                <span>BALANCE DUE:</span>
                                <span>$${parseFloat(balance_due).toFixed(2)}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Notes -->
                    ${notes ? `
                    <div class="notes-section">
                        <div class="section-title">Notes</div>
                        <p>${notes}</p>
                    </div>
                    ` : ''}

                    <!-- Footer -->
                    <div class="footer">
                        <p>This is a professional invoice document. Please contact us if you have any questions.</p>
                    </div>
                </div>
            </body>
            </html>
        `
    }
}

export default PDFExporter
