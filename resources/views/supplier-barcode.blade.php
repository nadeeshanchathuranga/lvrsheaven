<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Supplier Barcode – {{ $supplier->name }}</title>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <style>
    @page {
      size: 90mm auto;
      margin: 0;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: Arial, Helvetica, sans-serif;
      background: #f0f0f0;
    }

    /* ─── Screen toolbar ─── */
    .toolbar {
      background: #1e293b;
      color: #fff;
      padding: 14px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
    }
    .toolbar h1 { font-size: 16px; font-weight: 700; }
    .toolbar p  { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .btn-print {
      background: #16a34a; color: #fff;
      border: none; border-radius: 8px;
      padding: 10px 28px; font-size: 15px; font-weight: 700;
      cursor: pointer;
    }
    .btn-print:hover { background: #15803d; }

    .sheet-wrap {
      display: flex;
      justify-content: center;
      padding: 32px;
      overflow-x: auto;
    }

    /* Scale 3× on screen for readability; exact mm on print */
    .sticker-grid {
      display: grid;
      grid-template-columns: repeat(3, 30mm);
      grid-auto-rows: 16mm;
      transform: scale(3);
      transform-origin: top left;
    }

    @media print {
      .toolbar    { display: none; }
      .sheet-wrap { display: block; padding: 0; }
      body        { background: #fff; }
      .sticker-grid {
        transform: none;
        margin: 0;
      }
    }

    /* ─── Single sticker ─── */
    .sticker {
      width:  30mm;
      height: 16mm;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 1mm 1.5mm;
      border: 0.3mm solid #bbb;
      overflow: hidden;
    }

    .sticker-name {
      font-size: 4.5pt;
      font-weight: 700;
      text-align: center;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      width: 100%;
      max-width: 28mm;
      margin-bottom: 0.5mm;
    }

    .sticker svg {
      width: 27mm;
      height: 8mm;
    }

    .sticker-code {
      font-size: 4pt;
      text-align: center;
      margin-top: 0.5mm;
    }
  </style>
</head>
<body>

<div class="toolbar">
  <div>
    <h1>Supplier Barcode — {{ $supplier->name }}</h1>
    <p>Code: {{ $supplier->supplier_code }}</p>
  </div>
  <button class="btn-print" onclick="window.print()">Print</button>
</div>

<div class="sheet-wrap">
  <div class="sticker-grid" id="stickerGrid">
    {{-- 30 stickers per sheet --}}
    @for ($i = 0; $i < 30; $i++)
    <div class="sticker">
      <div class="sticker-name">{{ $supplier->name }}</div>
      <svg class="barcode-{{ $i }}"></svg>
      <div class="sticker-code">{{ $supplier->supplier_code }}</div>
    </div>
    @endfor
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const code = @json($supplier->supplier_code);
    for (let i = 0; i < 30; i++) {
      JsBarcode('.barcode-' + i, code, {
        format: 'CODE128',
        width: 1.2,
        height: 28,
        displayValue: false,
        margin: 0,
      });
    }

    // Compensate grid height for screen scale
    const grid = document.getElementById('stickerGrid');
    const rows = Math.ceil(30 / 3);
    grid.style.marginBottom = (rows * 16 * 2) + 'mm';
  });
</script>
</body>
</html>
