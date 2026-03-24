/**
 * Opens a minimal print popup containing only the receipt element.
 * @param {string} elementId  - DOM id of the receipt container
 * @param {string} title      - Window title / document title
 * @param {'80mm'|'58mm'|'A4'|'A5'} size - Paper size
 */
export function printPopup(elementId, title = 'Receipt', size = '80mm') {
    const el = document.getElementById(elementId)
    if (!el) return

    const sizeMap = {
        '80mm': '80mm auto',
        '58mm': '58mm auto',
        'A4':   '210mm 297mm',
        'A5':   '148mm 210mm',
    }
    const pageSize = sizeMap[size] || '80mm auto'
    const bodyWidth = (size === 'A4' || size === 'A5') ? '100%' : size

    const win = window.open('', '_blank', 'width=600,height=800')
    if (!win) { window.print(); return }

    win.document.write(`<!DOCTYPE html><html><head>
<meta charset="utf-8">
<title>${title}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
html,body{background:#fff;color:#000;font-family:'Courier New',monospace;width:${bodyWidth};}
body{padding:4mm;}
@page{size:${pageSize};margin:0;}
/* ── receipt layout ── */
.receipt-container{width:100%;max-width:100%;padding:0;box-shadow:none;border-radius:0;background:#fff;color:#000;}
.receipt-header{text-align:center;margin-bottom:10px;}
.receipt-logo{display:block;max-width:96px;max-height:72px;margin:0 auto 8px;object-fit:contain;}
.receipt-hotel-name{font-size:16px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;}
.receipt-address,.receipt-phone,.receipt-email{font-size:11px;margin:2px 0;}
.receipt-divider{border-top:1px dashed #000;margin:8px 0;}
.receipt-info,.receipt-items,.receipt-totals{margin-bottom:10px;}
.receipt-row{display:flex;justify-content:space-between;font-size:12px;margin:4px 0;}
.receipt-number{font-weight:bold;}
.receipt-items-header{display:flex;justify-content:space-between;font-weight:bold;font-size:11px;text-transform:uppercase;margin-bottom:6px;}
.receipt-item{margin-bottom:8px;}
.receipt-item-name{display:flex;align-items:center;gap:4px;font-weight:600;font-size:12px;margin-bottom:2px;}
.item-emoji{font-size:14px;}
.receipt-item-details{display:flex;justify-content:space-between;font-size:11px;color:#444;padding-left:18px;}
.receipt-item-quantity{flex:1;}
.receipt-item-total{font-weight:600;color:#000;}
.receipt-total-row{display:flex;justify-content:space-between;font-size:12px;margin:4px 0;}
.receipt-total-row.discount{color:#059669;}
.receipt-total-row.grand-total{font-size:15px;font-weight:bold;border-top:2px solid #000;border-bottom:2px solid #000;padding:6px 0;margin-top:8px;}
.receipt-footer{text-align:center;margin-top:12px;}
.receipt-thank-you{font-weight:bold;font-size:12px;margin:8px 0;}
.receipt-footer-text,.receipt-website-footer{font-size:11px;color:#555;margin-top:4px;}
/* ── checkin / checkout bill layout ── */
.text-center{text-align:center;} .text-right{text-align:right;}
.font-bold{font-weight:700;} .font-semibold{font-weight:600;} .font-medium{font-weight:500;} .font-mono{font-family:monospace;}
.text-2xl{font-size:18px;} .text-base{font-size:14px;} .text-sm{font-size:12px;} .text-xs{font-size:11px;} .text-\\[11px\\]{font-size:11px;} .text-\\[12px\\]{font-size:12px;}
.grid{display:grid;} .grid-cols-2{grid-template-columns:1fr 1fr;} .gap-4{gap:10px;} .gap-2{gap:6px;}
.flex{display:flex;} .justify-between{justify-content:space-between;} .items-center{align-items:center;}
.space-y-1>*+*{margin-top:3px;} .space-y-2>*+*{margin-top:6px;} .space-y-3>*+*{margin-top:8px;} .space-y-6>*+*{margin-top:14px;}
.border{border:1px solid #d1d5db;} .border-t{border-top:1px solid #d1d5db;} .border-b{border-bottom:1px solid #d1d5db;}
.border-t-2{border-top:2px solid #d1d5db;} .border-b-2{border-bottom:2px solid #d1d5db;}
.border-gray-200{border-color:#e5e7eb;} .border-gray-300{border-color:#d1d5db;}
.rounded-lg{border-radius:6px;} .rounded-md{border-radius:4px;} .overflow-hidden{overflow:hidden;}
.p-3{padding:8px;} .p-4{padding:10px;} .p-6{padding:14px;} .p-8{padding:18px;}
.px-3{padding-left:8px;padding-right:8px;} .px-4{padding-left:10px;padding-right:10px;}
.py-1{padding-top:3px;padding-bottom:3px;} .py-2{padding-top:5px;padding-bottom:5px;} .py-3{padding-top:8px;padding-bottom:8px;}
.pt-2{padding-top:5px;} .pt-4{padding-top:10px;} .pb-4{padding-bottom:10px;}
.mb-1{margin-bottom:3px;} .mb-2{margin-bottom:5px;} .mb-3{margin-bottom:8px;} .mb-6{margin-bottom:14px;}
.mt-1{margin-top:3px;} .mt-2{margin-top:5px;} .mt-3{margin-top:8px;} .mt-6{margin-top:14px;}
.mx-auto{margin-left:auto;margin-right:auto;}
.h-16{height:48px;} .max-w-xs{max-width:200px;} .object-contain{object-fit:contain;}
.leading-5{line-height:1.4;} .tabular-nums{font-variant-numeric:tabular-nums;}
.whitespace-nowrap{white-space:nowrap;} .min-w-0{min-width:0;} .flex-1{flex:1;} .truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.capitalize{text-transform:capitalize;}
.text-gray-500{color:#6b7280;} .text-gray-600{color:#4b5563;} .text-gray-700{color:#374151;} .text-gray-800{color:#1f2937;}
.text-blue-700{color:#1e40af;} .text-green-600{color:#059669;} .text-green-700{color:#15803d;} .text-red-600{color:#dc2626;}
.bg-gray-100{background:#f3f4f6;} .bg-gray-50,.bg-\\[\\#f9fafb\\]{background:#f9fafb;} .bg-\\[\\#eff6ff\\]{background:#eff6ff;}
</style>
</head><body>${el.innerHTML}</body></html>`)
    win.document.close()
    win.onload = () => { win.focus(); win.print(); win.onafterprint = () => win.close() }
}
