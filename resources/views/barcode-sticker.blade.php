<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Barcode Stickers – {{ $product->name }}</title>
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
    /*
      On SCREEN we scale up 3× so stickers are comfortable to view.
      On PRINT  we reset to exact mm and let the @page handle the rest.
    */
    .sticker-grid {
      display: grid;
      grid-template-columns: repeat(3, 30mm);   /* exactly 3 columns */
      grid-auto-rows: 16mm;
      /* scale up for screen readability */
      transform: scale(3);
      transform-origin: top left;
      /* compensate for the scale so the wrapper isn't clipped */
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
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 0.5mm 0.8mm;
      border: 0.3mm dashed #bbb;   /* cut-guide on screen */
      background: #fff;
    }

    @media print {
      .sticker {
        border: none;          /* no visible border on the actual sticker */
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
  </style>
</head>
<body>

  <div class="toolbar">
    <div>
      <h1>{{ $product->name }}</h1>
      <p>{{ $qty }} sticker(s) &nbsp;·&nbsp; 30 mm × 16 mm &nbsp;·&nbsp; 3-column roll</p>
    </div>
    <button class="btn-print" onclick="window.print()">🖨&nbsp; Print Stickers</button>
  </div>

  @php
    $barcode = $product->barcode ?: ($product->code ?: (string)$product->id);
    $name    = $product->name ?? '';
    $price   = number_format($product->selling_price ?? 0, 2);
    $rows    = ceil($qty / 3);
  @endphp

  {{-- CSS variable to compensate transform scale height --}}
  <style>
    .sticker-grid { --rows: {{ $rows }}; }
    .sheet-wrap   { min-height: calc({{ $rows }} * 16mm * 3 + 80px); }
  </style>

  <div class="sheet-wrap">
    <div class="sticker-grid">
      @for ($i = 0; $i < $qty; $i++)
      <div class="sticker">
        <div class="sticker-name" title="{{ $name }}">{{ $name }}</div>
        <svg id="bc-{{ $i }}" data-barcode="{{ $barcode }}"></svg>
        <div class="sticker-price">Rs. {{ $price }}</div>
        <div class="sticker-code">{{ $barcode }}</div>
      </div>
      @endfor
    </div>
  </div>

  <script>
    document.querySelectorAll('svg[data-barcode]').forEach(function(el) {
      try {
        JsBarcode(el, el.getAttribute('data-barcode'), {
          format:       'CODE128',
          width:        1.0,
          height:       26,
          displayValue: false,
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

    /* ── Print page setup ───────────────────────── */
    @page {
      /* 3 stickers wide: 3 × 30mm = 90mm, auto height feeds the roll */
      size: 90mm auto;
      margin: 0;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: #f5f5f5;
      font-family: Arial, Helvetica, sans-serif;
    }

    /* ── Screen-only controls ───────────────────── */
    .screen-bar {
      background: #1e293b;
      color: #fff;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .screen-bar h1 { font-size: 15px; font-weight: 700; }
    .screen-bar span { font-size: 13px; color: #94a3b8; }
    .btn-print {
      background: #16a34a;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 8px 22px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      letter-spacing: 0.5px;
    }
    .btn-print:hover { background: #15803d; }

    @media print {
      .screen-bar { display: none; }
      body { background: #fff; }
    }

    /* ── Sticker grid ───────────────────────────── */
    .sticker-grid {
      display: flex;
      flex-wrap: wrap;
      width: 90mm;           /* 3 columns × 30mm */
      margin: 12px auto;
      background: #fff;
      border: 1px dashed #ccc;
    }

    @media print {
      .sticker-grid {
        width: 90mm;
        margin: 0;
        border: none;
      }
    }

    /* ── Single sticker ─────────────────────────── */
    .sticker {
      width: 30mm;
      height: 16mm;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 0.4mm 0.6mm;
      border: 0.2mm solid #e2e8f0;  /* faint guide line – invisible on print */
      background: #fff;
    }

    @media print {
      .sticker {
        border: none;
        /* hard-cut between stickers – no bleed */
        break-inside: avoid;
        page-break-inside: avoid;
      }
    }

    .sticker-name {
      font-size: 4.5pt;
      font-weight: 700;
      color: #000;
      text-align: center;
      line-height: 1.15;
      max-width: 29mm;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      margin-bottom: 0.3mm;
    }

    .sticker svg {
      /* JsBarcode fills whatever width/height we give it */
      display: block;
      max-width: 28mm;
      height: 7.5mm;
    }

    .sticker-price {
      font-size: 5pt;
      font-weight: 700;
      color: #000;
      margin-top: 0.3mm;
      letter-spacing: 0.3pt;
    }

    .sticker-code {
      font-size: 3.8pt;
      color: #444;
      margin-top: 0.1mm;
      letter-spacing: 0.2pt;
    }
  </style>
</head>
<body>

  <!-- ── Screen controls ── -->
  <div class="screen-bar">
    <div>
      <h1>{{ $product->name }}</h1>
      <span>{{ $qty }} sticker(s) · 30 × 16 mm · 3-column roll</span>
    </div>
    <button class="btn-print" onclick="window.print()">🖨 Print</button>
  </div>

  <!-- ── Sticker sheet ── -->
  <div class="sticker-grid" id="grid">
    @php
      $barcode = $product->barcode ?: ($product->code ?: $product->id);
      $name    = $product->name ?? '';
      $price   = number_format($product->selling_price ?? 0, 2);
    @endphp

    @for ($i = 0; $i < $qty; $i++)
    <div class="sticker">
      <div class="sticker-name" title="{{ $name }}">{{ $name }}</div>
      {{-- JsBarcode targets each svg by unique id --}}
      <svg id="bc-{{ $i }}" data-barcode="{{ $barcode }}"></svg>
      <div class="sticker-price">Rs. {{ $price }}</div>
      <div class="sticker-code">{{ $barcode }}</div>
    </div>
    @endfor
  </div>

  <script>
    document.querySelectorAll('svg[data-barcode]').forEach(function(el) {
      try {
        JsBarcode(el, el.getAttribute('data-barcode'), {
          format:      'CODE128',
          width:       1.1,
          height:      30,       // px – scaled by CSS mm
          displayValue: false,   // we show it as text below
          margin:      1,
          lineColor:   '#000',
          background:  '#fff',
        });
      } catch(e) {
        // Fallback: show plain text if barcode is invalid
        el.outerHTML = '<span style="font-size:5pt;color:red">Invalid</span>';
      }
    });
  </script>
</body>
</html>
