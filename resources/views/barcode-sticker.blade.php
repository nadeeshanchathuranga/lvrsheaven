<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Barcode Stickers – {{ $product->name }}</title>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <style>
    @page { size: auto; margin: 0; }

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
      flex-direction: column;
      align-items: center;
      padding: 32px;
      overflow-x: auto;
    }

    /* ─── Row of 3 stickers ─── */
    .sticker-row {
      display: flex;
      width: fit-content;
    }

    /* Screen-only: scale up so stickers are comfortable to preview */
    @media screen {
      .sheet-wrap {
        zoom: 3;
      }
    }

    @media print {
      .toolbar    { display: none; }
      .sheet-wrap { display: block; padding: 0; margin: 0; overflow: visible; }
      body        { background: #fff; margin: 0; padding: 0; }
    }

    /* ─── Single sticker ─── */
    .sticker {
      width:  30mm;
      height: 16mm;
      min-height: 0;
      max-height: 16mm;
      flex-shrink: 0;
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
      font-size: 3.5pt;
      font-weight: 700;
      color: #000;
      text-align: center;
      line-height: 1.1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 26mm;
    }

    .sticker svg {
      display: block;
      width: 26mm;
      height: 5.5mm;
      max-height: 5.5mm;
      overflow: hidden;
    }

    .sticker-price {
      font-size: 4pt;
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

  <div class="sheet-wrap">
    @for ($i = 0; $i < $qty; $i++)
      @if ($i % 3 === 0)<div class="sticker-row">@endif
      <div class="sticker">
        <div class="sticker-name" title="{{ $name }}">{{ $name }}</div>
        <svg id="bc-{{ $i }}" data-barcode="{{ $barcode }}"></svg>
        <div class="sticker-price">Rs. {{ $price }}</div>
        @if($supplierCode)<div class="sticker-supplier">{{ $supplierCode }}</div>@endif
      </div>
      @if ($i % 3 === 2 || $i === $qty - 1)</div>@endif
    @endfor
  </div>

  <script>
    document.querySelectorAll('svg[data-barcode]').forEach(function(el) {
      try {
        JsBarcode(el, el.getAttribute('data-barcode'), {
          format:       'CODE128',
          width:        0.7,
          height:       14,
          displayValue: true,
          fontSize:     5,
          margin:       0,
          lineColor:    '#000',
          background:   '#fff',
        });
        el.removeAttribute('height');
        el.removeAttribute('width');
        el.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        el.style.height = '5.5mm';
        el.style.width  = '26mm';
      } catch(e) {
        el.insertAdjacentHTML('afterend', '<span style="font-size:4pt;color:red">ERR</span>');
        el.remove();
      }
    });
  </script>
</body>
</html>
