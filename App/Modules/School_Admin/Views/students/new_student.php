<?php
require_once __DIR__ . '/../../../../Config/auth_check_school_admin.php';
require_once __DIR__ . '/../../../../Core/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>New Student - School Admin</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="Register new student" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" href="../../../../../public/assets/img/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="../../../../../public/assets/css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .tab-card { border-radius:8px; margin: 24px auto; max-width: 980px; }
        .form-section { padding:12px 0 }
        .thumb-preview { width:96px; height:96px; object-fit:cover; border-radius:6px; border:1px solid #ddd }
        .nav-tabs .nav-link { font-weight:600 }
        /* Make labels dark and readable */
        .tab-card label, .tab-card .form-group label { color: #111 !important; font-weight:600; }
    </style>
</head>
<body>
    <div class="app">
        <div class="app-wrap">
           
            <div class="app-container">
                <div class="" id="main">
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4 class="mb-3">Register New Student</h4>
                            </div>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-lg-8 col-md-10">
                                <div class="card tab-card">
                                    <div class="card-body">
                                        <ul class="nav nav-tabs" id="studentTabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="tab-personal-tab" data-toggle="tab" href="#tab-personal" role="tab">Personal Details</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-academic-tab" data-toggle="tab" href="#tab-academic" role="tab">Academic Details</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="tab-docs-tab" data-toggle="tab" href="#tab-docs" role="tab">Documents</a>
                                            </li>
                                        </ul>
                                        <form id="newStudentForm" onsubmit="return false;">
                                        <div class="tab-content pt-3">
                                            <div class="tab-pane fade show active" id="tab-personal" role="tabpanel">
                                                <div class="form-section">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4">
                                                            <label>First Name</label>
                                                            <input class="form-control" id="first_name" placeholder="First name">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label>Last Name</label>
                                                            <input class="form-control" id="last_name" placeholder="Last name">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label>Date of Birth</label>
                                                            <input class="form-control" id="dob" type="date">
                                                        </div>
                                                    </div>

                                                    <div class="form-row">
                                                        <div class="form-group col-md-3">
                                                            <label>Gender</label>
                                                            <select class="form-control" id="gender"><option value="">-- choose --</option><option value="male">Male</option><option value="female">Female</option></select>
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label>Admission No</label>
                                                            <input class="form-control" id="admission_no">
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label>Admission Date</label>
                                                            <input class="form-control" id="admission_date" type="date">
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label>Religion (optional)</label>
                                                            <input class="form-control" id="religion">
                                                        </div>
                                                    </div>

                                                    <hr>
                                                    <h6>Primary Guardian</h6>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4"><label>Name</label><input class="form-control" id="guardian_name"></div>
                                                        <div class="form-group col-md-2"><label>Relation</label><input class="form-control" id="guardian_relation" placeholder="Father/Mother"></div>
                                                        <div class="form-group col-md-3"><label>CNIC / Passport</label><input class="form-control" id="guardian_cnic"></div>
                                                        <div class="form-group col-md-3"><label>Occupation</label><input class="form-control" id="guardian_occupation"></div>
                                                    </div>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4"><label>Mobile</label><input class="form-control" id="guardian_mobile"></div>
                                                        <div class="form-group col-md-8"><label>Address</label><input class="form-control" id="guardian_address"></div>
                                                    </div>

                                                    <hr>
                                                    <h6>Secondary Guardian (optional)</h6>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4"><label>Name</label><input class="form-control" id="guardian2_name"></div>
                                                        <div class="form-group col-md-2"><label>Relation</label><input class="form-control" id="guardian2_relation"></div>
                                                        <div class="form-group col-md-3"><label>CNIC / Passport</label><input class="form-control" id="guardian2_cnic"></div>
                                                        <div class="form-group col-md-3"><label>Occupation</label><input class="form-control" id="guardian2_occupation"></div>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end">
                                                    <button class="btn btn-primary" id="toAcademic">Next: Academic</button>
                                                </div>
                                            </div>

                                            <div class="tab-pane fade" id="tab-academic" role="tabpanel">
                                                <div class="form-section">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4"><label>Class to enroll</label><select id="enroll_class" class="form-control"><option value="">-- choose --</option></select></div>
                                                        <div class="form-group col-md-4"><label>Section</label><select id="enroll_section" class="form-control"><option value="">-- choose --</option></select></div>
                                                        <div class="form-group col-md-4"><label>Session</label><select id="enroll_session" class="form-control"><option value="">-- choose --</option></select></div>
                                                    </div>

                                                    <div class="form-row">
                                                        <div class="form-group col-md-6"><label>Was transferred?</label><select id="transferred" class="form-control"><option value="no">No</option><option value="yes">Yes</option></select></div>
                                                        <div class="form-group col-md-6"><label>Previous School (if transferred)</label><input class="form-control" id="prev_school"></div>
                                                    </div>

                                                    <div class="form-row">
                                                        <div class="form-group col-md-4"><label>Previous Class</label><input class="form-control" id="prev_class"></div>
                                                        <div class="form-group col-md-4"><label>Previous Admission No</label><input class="form-control" id="prev_adm_no"></div>
                                                        <div class="form-group col-md-4"><label>Last Exam Result (optional)</label><input class="form-control" id="prev_result"></div>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <button class="btn btn-outline-secondary" id="backToPersonal">Back</button>
                                                    <button class="btn btn-primary" id="toDocs">Next: Documents</button>
                                                </div>
                                            </div>

                                            <div class="tab-pane fade" id="tab-docs" role="tabpanel">
                                                <div class="form-section">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4 text-center">
                                                            <label>Student Photo</label>
                                                            <div><img id="preview_photo" class="thumb-preview mb-2" src="../../../../../public/assets/img/avtar/02.jpg" alt="photo"></div>
                                                            <input type="file" id="doc_photo" class="form-control-file">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label>Guardian CNIC / Passport (scan)</label>
                                                            <input type="file" id="doc_guardian_cnic" class="form-control-file">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label>Birth Certificate / Form B</label>
                                                            <input type="file" id="doc_birth_cert" class="form-control-file">
                                                        </div>
                                                    </div>

                                                    <div class="form-row">
                                                        <div class="form-group col-md-6"><label>Other Documents</label><input type="file" id="doc_other" class="form-control-file" multiple></div>
                                                        <div class="form-group col-md-6"><label>Notes</label><textarea id="doc_notes" class="form-control" placeholder="Optional notes about documents"></textarea></div>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <button class="btn btn-outline-secondary" id="backToAcademic">Back</button>
                                                    <div>
                                                        <button class="btn btn-secondary mr-2" id="saveDraft">Save Draft</button>
                                                        <button class="btn btn-success" id="submitStudent">Submit Student</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../../../../public/assets/js/vendors.js"></script>
    <script src="../../../../../public/assets/js/app.js"></script>
    <script>
    (function(){
        // tab navigation handlers
        document.getElementById('toAcademic').addEventListener('click', function(e){ e.preventDefault(); $('#tab-academic-tab').tab('show'); });
        document.getElementById('toDocs').addEventListener('click', function(e){ e.preventDefault(); $('#tab-docs-tab').tab('show'); });
        document.getElementById('backToPersonal').addEventListener('click', function(e){ e.preventDefault(); $('#tab-personal-tab').tab('show'); });
        document.getElementById('backToAcademic').addEventListener('click', function(e){ e.preventDefault(); $('#tab-academic-tab').tab('show'); });

        document.getElementById('submitStudent').addEventListener('click', function(){
            alert('Submit student (UI only) - implement save_student.php to persist.');
        });

        document.getElementById('saveDraft').addEventListener('click', function(){ alert('Draft saved (placeholder)'); });

        // preview photo
        var photo = document.getElementById('doc_photo');
        if (photo) photo.addEventListener('change', function(e){
            var f = this.files && this.files[0]; if (!f) return;
            var reader = new FileReader(); reader.onload = function(ev){ document.getElementById('preview_photo').src = ev.target.result; };
            reader.readAsDataURL(f);
        });
    })();
    </script>
</body>
</html>
