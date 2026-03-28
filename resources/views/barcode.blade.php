<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Barcode – {{ $product->name }}</title>
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <style>
    @page { size: A4; margin: 10mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: Arial, Helvetica, sans-serif;
      background: #f0f0f0;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
    }
    .toolbar {
      width: 100%;
      background: #1e293b;
      color: #fff;
      padding: 14px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
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
    @media print {
      .toolbar { display: none; }
      body { background: #fff; }
      .card { margin-top: 0; border: none; box-shadow: none; padding: 0; }
    }
    .card {
      margin-top: 40px;
      background: #fff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 24px 32px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    .product-name  { font-size: 16pt; font-weight: 700; color: #111; text-align: center; }
    .product-code  { font-size: 10pt; color: #555; letter-spacing: 0.5pt; }
    svg#barcode    { display: block; width: 80mm; height: 25mm; }
    .product-price { font-size: 14pt; font-weight: 800; color: #111; }
    .supplier-code { font-size: 9pt; color: #888; letter-spacing: 0.4pt; }
  </style>
</head>
<body>

  <div class="toolbar">
    <div>
      <h1>{{ $product->name }}</h1>
      <p>Single barcode view</p>
    </div>
    <button class="btn-print" onclick="window.print()">🖨&nbsp; Print</button>
  </div>

  @php
    $barcode      = $product->barcode ?: ($product->code ?: (string)$product->id);
    $price        = number_format($product->selling_price ?? 0, 2);
    $supplierCode = (isset($product->supplier->supplier_code) && trim($product->supplier->supplier_code) !== '')
                    ? trim($product->supplier->supplier_code)
                    : null;
  @endphp

  <div class="card">
    <div class="product-name">{{ $product->name }}</div>
    <div class="product-code">{{ $barcode }}</div>
    <svg id="barcode"></svg>
    <div class="product-price">Rs. {{ $price }}</div>
    @if($supplierCode)
    <div class="supplier-code">Supplier: {{ $supplierCode }}</div>
    @endif
  </div>

  <script>
    try {
      JsBarcode('#barcode', '{{ $barcode }}', {
        format:       'CODE128',
        width:        2,
        height:       60,
        displayValue: false,
        margin:       4,
        lineColor:    '#000',
        background:   '#fff',
      });
    } catch(e) {
      document.getElementById('barcode').insertAdjacentHTML('afterend',
        '<p style="color:red;font-size:12pt">Invalid barcode value</p>');
    }
  </script>
</body>
</html>
