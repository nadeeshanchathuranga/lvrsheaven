<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bulk Barcode Stickers</title>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <style>
    /* ─── Print page: exact roll width = 3 × 30mm ─── */
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

    /* ─── Wrapper: centres the grid on screen ─── */
    .sheet-wrap {
      display: flex;
      justify-content: center;
      padding: 32px;
      overflow-x: auto;
    }

    /* ─── The sticker grid ─── */
    .sticker-grid {
      display: grid;
      grid-template-columns: repeat(3, 30mm);
      grid-auto-rows: 16mm;
      transform: scale(3);
      transform-origin: top left;
      margin-bottom: calc((16mm * var(--rows) * 2));
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
      overflow: hidden;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 0.5mm 0.8mm;
      border: 0.3mm dashed #bbb;
      background: #fff;
    }

    .sticker.has-supplier {
      padding-right: 5mm;
    }

    @media print {
      .sticker {
        border: none;
        page-break-inside: avoid;
        break-inside: avoid;
      }
    }

    .sticker-name {
      font-size: 4.2pt;
      font-weight: 700;
      color: #000;
      text-align: center;
      line-height: 1.2;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 28.5mm;
      margin-bottom: 0.4mm;
    }

    .sticker svg {
      display: block;
      width:  28mm;
      height: 8mm;
    }

    .sticker-price {
      font-size: 5pt;
      font-weight: 800;
      color: #000;
      margin-top: 0.3mm;
    }

    .sticker-code {
      font-size: 3.5pt;
      color: #555;
      margin-top: 0.1mm;
      letter-spacing: 0.3pt;
    }

    /* ─── Right supplier code strip ─── */
    .sticker-supplier {
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      width: 4.5mm;
      display: flex;
      align-items: center;
      justify-content: center;
      writing-mode: vertical-rl;
      transform: rotate(180deg);
      font-size: 3.5pt;
      font-weight: 600;
      color: #555;
      letter-spacing: 0.4pt;
      border-left: 0.2mm solid #ddd;
      overflow: hidden;
      white-space: nowrap;
      background: #fff;
    }

    @media print {
      .sticker-supplier {
        border-left: 0.2mm solid #ccc;
      }
    }
  </style>
</head>
<body>

  @php
    $totalStickers = 0;
    foreach ($products as $product) {
      $totalStickers += max(1, $product->stock_quantity ?? 0);
    }
    $rows = ceil($totalStickers / 3);
  @endphp

  <div class="toolbar">
    <div>
      <h1>Bulk Barcode Print</h1>
      <p>{{ count($products) }} product(s) &nbsp;·&nbsp; {{ $totalStickers }} sticker(s) &nbsp;·&nbsp; 30 mm × 16 mm &nbsp;·&nbsp; 3-column roll</p>
    </div>
    <button class="btn-print" onclick="window.print()">🖨&nbsp; Print All Stickers</button>
  </div>

  <style>
    .sticker-grid { --rows: {{ $rows }}; }
    .sheet-wrap   { min-height: calc({{ $rows }} * 16mm * 3 + 80px); }
  </style>

  <div class="sheet-wrap">
    <div class="sticker-grid">
      @php $stickerIndex = 0; @endphp
      @foreach ($products as $product)
        @php
          $barcode = $product->barcode ?: ($product->code ?: (string)$product->id);
          $name = $product->name ?? '';
          $price = number_format($product->selling_price ?? 0, 2);
          $qty = max(1, $product->stock_quantity ?? 0);
          $supplierCode = (isset($product->supplier->supplier_code) && trim($product->supplier->supplier_code) !== '') 
                          ? trim($product->supplier->supplier_code) 
                          : null;
        @endphp
        
        @for ($i = 0; $i < $qty; $i++)
        <div class="sticker {{ $supplierCode ? 'has-supplier' : '' }}">
          <div class="sticker-name" title="{{ $name }}">{{ $name }}</div>
          <svg id="bc-{{ $stickerIndex++ }}" data-barcode="{{ $barcode }}"></svg>
          <div class="sticker-price">Rs. {{ $price }}</div>
          @if($supplierCode)
          <div class="sticker-supplier">{{ $supplierCode }}</div>
          @endif
        </div>
        @endfor
      @endforeach
    </div>
  </div>

  <script>
    document.querySelectorAll('svg[data-barcode]').forEach(function(el) {
      try {
        JsBarcode(el, el.getAttribute('data-barcode'), {
          format:       'CODE128',
          width:        1.0,
          height:       26,
          displayValue: true,
          fontSize:     8,
          margin:       1,
          lineColor:    '#000',
          background:   '#fff',
        });
      } catch(e) {
        el.insertAdjacentHTML('afterend', '<span style="font-size:4pt;color:red">ERR</span>');
        el.remove();
      }
    });
  </script>
</body>
</html>
