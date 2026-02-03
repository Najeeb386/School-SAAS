<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Invoice Generator</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
body{background:#f6f7fb}
.card{border-radius:10px}
.summary-box{background:#fff;border-radius:10px;padding:20px;box-shadow:0 5px 15px rgba(0,0,0,.08)}
.table td{vertical-align:middle}
</style>
</head>

<body>

<div class="container my-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="mb-1">Invoice Generator</h3>
    <small class="text-muted">Generate student fee invoices</small>
  </div>
  <span class="badge badge-primary px-3 py-2">Session 2025–2026</span>
</div>

<!-- TABS -->
<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" data-toggle="tab" href="#auto">Auto Generate</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#manual">Manual Invoice</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-toggle="tab" href="#preview">Preview Queue</a>
  </li>
</ul>

<div class="tab-content">

<!-- ================= AUTO GENERATE TAB ================= -->
<div class="tab-pane fade show active" id="auto">

<div class="card">
<div class="card-body">

<h5 class="mb-3">Auto Monthly Invoice Generation</h5>

<div class="row">
  <div class="col-md-3">
    <label>Invoice Month</label>
    <input type="month" class="form-control">
  </div>

  <div class="col-md-3">
    <label>Apply To</label>
    <select class="form-control">
      <option>All Classes</option>
      <option>Selected Class</option>
    </select>
  </div>

  <div class="col-md-3">
    <label>Class</label>
    <select class="form-control">
      <option>Class 1</option>
      <option>Class 2</option>
    </select>
  </div>

  <div class="col-md-3">
    <label>Fee Type</label>
    <select class="form-control">
      <option>Monthly</option>
      <option>Yearly</option>
    </select>
  </div>
</div>

<hr>

<div class="text-right">
  <button class="btn btn-outline-secondary">Preview</button>
  <button class="btn btn-success">Generate Invoices</button>
</div>

</div>
</div>
</div>

<!-- ================= MANUAL INVOICE TAB ================= -->
<div class="tab-pane fade" id="manual">

<div class="row">

<!-- LEFT -->
<div class="col-md-8">
<div class="card mb-3">
<div class="card-body">

<h5 class="mb-3">Manual Invoice Creation</h5>

<div class="row mb-3">
  <div class="col-md-6">
    <label>Student</label>
    <input class="form-control" placeholder="Search student">
  </div>
  <div class="col-md-3">
    <label>Session</label>
    <select class="form-control">
      <option>2025–2026</option>
    </select>
  </div>
</div>

<table class="table table-bordered">
<thead class="thead-light">
<tr>
<th>Fee Item</th>
<th width="120">Amount</th>
<th width="60"></th>
</tr>
</thead>
<tbody>
<tr>
<td>Tuition Fee</td>
<td>5000</td>
<td><button class="btn btn-sm btn-danger">×</button></td>
</tr>
<tr>
<td>Exam Fee</td>
<td>1000</td>
<td><button class="btn btn-sm btn-danger">×</button></td>
</tr>
</tbody>
</table>

<button class="btn btn-outline-primary btn-sm">
<i class="fas fa-plus"></i> Add Fee Item
</button>

</div>
</div>
</div>

<!-- RIGHT -->
<div class="col-md-4">
<div class="summary-box">

<h6 class="text-muted">Invoice Summary</h6>
<hr>
<div class="d-flex justify-content-between">
<span>Subtotal</span><strong>₨ 6,000</strong>
</div>
<div class="d-flex justify-content-between">
<span>Discount</span><strong>₨ 0</strong>
</div>
<hr>
<div class="d-flex justify-content-between">
<strong>Total</strong><strong>₨ 6,000</strong>
</div>

<button class="btn btn-success btn-block mt-3">
Generate Invoice
</button>

</div>
</div>

</div>
</div>

<!-- ================= PREVIEW TAB ================= -->
<div class="tab-pane fade" id="preview">

<div class="card">
<div class="card-body">

<h5 class="mb-3">Invoice Preview Queue</h5>

<table class="table table-striped">
<thead>
<tr>
<th>Student</th>
<th>Class</th>
<th>Month</th>
<th>Amount</th>
<th></th>
</tr>
</thead>
<tbody>
<tr>
<td>Ali</td>
<td>Class 2</td>
<td>Aug 2026</td>
<td>₨ 5,000</td>
<td><button class="btn btn-sm btn-danger">Remove</button></td>
</tr>
</tbody>
</table>

<button class="btn btn-success float-right">
Generate Selected
</button>

</div>
</div>
</div>

</div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
