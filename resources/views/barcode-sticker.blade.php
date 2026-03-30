<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Barcode Stickers – {{ $product->name }}</title>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <style>
    /* ─── Print page: one row of 3 stickers per 'page' on continuous roll ─── */
    @page {
      size: 96mm 16mm;
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
      grid-template-columns: repeat(3, 30mm);   /* exactly 3 columns */
      grid-auto-rows: 16mm;
      row-gap: 0;                               /* continuous roll – no vertical gap */
      column-gap: 3mm;                          /* 3 cols × 30mm + 2 gaps × 3mm = 96mm */
    }

    /* Screen-only: scale up so stickers are comfortable to preview */
    @media screen {
      .sticker-grid {
        zoom: 3;
      }
    }

    @media print {
      .toolbar    { display: none; }
      .sheet-wrap { display: block; padding: 0; margin: 0; overflow: visible; }
      body        { background: #fff; width: 96mm; margin: 0; padding: 0; }
      .sticker-grid {
        margin: 0;
        padding: 0;
        width: 96mm;
      }
    }

    /* ─── Single sticker ─── */
    .sticker {
      width:  30mm;
      height: 16mm;
      min-height: 0;          /* override grid default min-height:auto */
      max-height: 16mm;
      overflow: hidden;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      border: 0.3mm dashed #bbb;
      background: #fff;
    }

    @media print {
      .sticker {
        border: none;
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
      max-width: 28mm;
    }

    .sticker svg {
      display: block;
      width: 28mm;
      height: 7mm;
      max-height: 7mm;
      overflow: hidden;
    }

    .sticker-price {
      font-size: 5pt;
      font-weight: 800;
      color: #000;
    }

    .sticker-supplier {
      position: absolute;
      right: 0.5mm;
      top: 0.5mm;
      font-size: 3pt;
      color: #555;
      margin: 0;
      padding: 0;
      line-height: 1;
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
    $barcode      = $product->barcode ?: ($product->code ?: (string)$product->id);
    $name         = $product->name ?? '';
    $price        = number_format($product->selling_price ?? 0, 2);
    $supplierCode = (isset($product->supplier->supplier_code) && trim($product->supplier->supplier_code) !== '') 
                    ? trim($product->supplier->supplier_code) 
                    : null;
  @endphp

  {{-- No extra height hacks needed – zoom (unlike transform) respects layout flow --}}

  <div class="sheet-wrap">
    <div class="sticker-grid">
      @for ($i = 0; $i < $qty; $i++)
      <div class="sticker">
        <div class="sticker-name" title="{{ $name }}">{{ $name }}</div>
        <svg id="bc-{{ $i }}" data-barcode="{{ $barcode }}"></svg>
        <div class="sticker-price">Rs. {{ $price }}</div>
        @if($supplierCode)<div class="sticker-supplier">{{ $supplierCode }}</div>@endif
      </div>
      @endfor
    </div>
  </div>

  <script>
    document.querySelectorAll('svg[data-barcode]').forEach(function(el) {
      try {
        JsBarcode(el, el.getAttribute('data-barcode'), {
          format:       'CODE128',
          width:        0.9,
          height:       16,
          displayValue: true,
          fontSize:     6,
          margin:       1,
          lineColor:    '#000',
          background:   '#fff',
        });
        el.removeAttribute('height');
        el.removeAttribute('width');
        el.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        el.style.height = '7mm';
        el.style.width  = '28mm';
      } catch(e) {
        el.insertAdjacentHTML('afterend', '<span style="font-size:4pt;color:red">ERR</span>');
        el.remove();
      }
    });
  </script>
</body>
</html>
