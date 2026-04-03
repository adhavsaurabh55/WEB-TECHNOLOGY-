<?php
$servername = "127.0.0.1";
$username   = "root";
$password   = "";
$dbname     = "employee_db";
$port       = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("<div style='font-family:sans-serif;padding:40px;color:#c0392b'>Connection failed: " . $conn->connect_error . "</div>");
}

$page        = $_GET['page'] ?? 'home';
$message     = "";
$messageType = "";
$tableHTML   = "";
$prefill     = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $e = fn($v) => $conn->real_escape_string($v ?? '');
    $employeeID = $e($_POST['employee_id'] ?? '');
    $firstName  = $e($_POST['first_name']  ?? '');
    $lastName   = $e($_POST['last_name']   ?? '');
    $gender     = $e($_POST['gender']      ?? '');
    $email      = $e($_POST['email']       ?? '');
    $phone      = $e($_POST['phone']       ?? '');
    $age        = intval($_POST['age']      ?? 0);
    $pincode    = $e($_POST['pincode']     ?? '');
    $state      = $e($_POST['state']       ?? '');
    $country    = $e($_POST['country']     ?? '');
    $department = $e($_POST['department']  ?? '');
    $role       = $e($_POST['role']        ?? '');
    $salary     = floatval($_POST['salary'] ?? 0);
    $dob        = $e($_POST['dob']         ?? '');
    $address    = $e($_POST['address']     ?? '');

    if (isset($_POST['do_add'])) {
        $sql = "INSERT INTO employees VALUES ('$employeeID','$firstName','$lastName','$gender','$email','$phone','$age','$pincode','$state','$country','$department','$role','$salary','$dob','$address')";
        if ($conn->query($sql) === TRUE) { $message = "Employee <strong>$firstName $lastName</strong> was added successfully."; $messageType = "success"; }
        else { $message = $conn->error; $messageType = "error"; }
        $page = "add";
    }
    elseif (isset($_POST['do_edit_lookup'])) {
        $lid = $e($_POST['lookup_id'] ?? '');
        $res = $conn->query("SELECT * FROM employees WHERE employee_id='$lid' LIMIT 1");
        if ($res && $res->num_rows > 0) { $prefill = $res->fetch_assoc(); $page = "edit_form"; }
        else { $message = "No employee found with ID \"$lid\"."; $messageType = "error"; $page = "edit"; }
    }
    elseif (isset($_POST['do_edit_save'])) {
        $sql = "UPDATE employees SET gender='$gender',email='$email',phone='$phone',age='$age',pincode='$pincode',state='$state',country='$country',department='$department',role='$role',salary='$salary',dob='$dob',address='$address' WHERE employee_id='$employeeID'";
        if ($conn->query($sql) === TRUE) { $message = "Record updated successfully."; $messageType = "success"; $page = "edit"; }
        else { $message = $conn->error; $messageType = "error"; $page = "edit_form"; $r2 = $conn->query("SELECT * FROM employees WHERE employee_id='$employeeID' LIMIT 1"); if ($r2) $prefill = $r2->fetch_assoc(); }
    }
    elseif (isset($_POST['do_delete_lookup'])) {
        $lid = $e($_POST['lookup_id'] ?? '');
        $res = $conn->query("SELECT * FROM employees WHERE employee_id='$lid' LIMIT 1");
        if ($res && $res->num_rows > 0) { $prefill = $res->fetch_assoc(); $page = "delete_confirm"; }
        else { $message = "No employee found with ID \"$lid\"."; $messageType = "error"; $page = "delete"; }
    }
    elseif (isset($_POST['do_delete_confirm'])) {
        $did = $e($_POST['employee_id'] ?? '');
        if ($conn->query("DELETE FROM employees WHERE employee_id='$did'") === TRUE) { $message = "Employee record has been permanently deleted."; $messageType = "success"; }
        else { $message = $conn->error; $messageType = "error"; }
        $page = "delete";
    }
}

if ($page === 'display') {
    $result = $conn->query("SELECT * FROM employees ORDER BY employee_id");
    if ($result && $result->num_rows > 0) {
        $cnt = $result->num_rows;
        $message = "$cnt employee record" . ($cnt > 1 ? "s" : "") . " found.";
        $messageType = "info";
        $tableHTML = "<table><thead><tr><th>Employee ID</th><th>Full Name</th><th>Gender</th><th>Email</th><th>Phone</th><th>Age</th><th>Department</th><th>Role</th><th>Annual Salary</th></tr></thead><tbody>";
        while ($row = $result->fetch_assoc()) {
            $ini = strtoupper(substr($row['first_name'],0,1) . substr($row['last_name'],0,1));
            $tableHTML .= "<tr>
              <td><span class='eid'>".htmlspecialchars($row['employee_id'])."</span></td>
              <td><div class='name-cell'><div class='av'>$ini</div><div class='nm'>".htmlspecialchars($row['first_name'])." ".htmlspecialchars($row['last_name'])."</div></div></td>
              <td>".htmlspecialchars($row['gender'])."</td>
              <td class='em'>".htmlspecialchars($row['email'])."</td>
              <td>".htmlspecialchars($row['phone'])."</td>
              <td>".htmlspecialchars($row['age'])."</td>
              <td><span class='dept'>".htmlspecialchars($row['department'])."</span></td>
              <td>".htmlspecialchars($row['role'])."</td>
              <td class='sal'>&#8377;".number_format($row['salary'],2)."</td>
            </tr>";
        }
        $tableHTML .= "</tbody></table>";
    } else { $message = "No records found in the database."; $messageType = "info"; }
}

function h($v) { return htmlspecialchars($v ?? ''); }

$navItems = [
    'home'    => ['icon'=>'M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'label'=>'Dashboard'],
    'add'     => ['icon'=>'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M12 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M19 8v6 M22 11h-6', 'label'=>'Add Employee'],
    'edit'    => ['icon'=>'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7 M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z', 'label'=>'Edit Employee'],
    'delete'  => ['icon'=>'M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6 M3 6h18 M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2', 'label'=>'Remove Employee'],
    'display' => ['icon'=>'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2 M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M23 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75', 'label'=>'All Employees'],
];
$activePage = in_array($page, ['edit_form']) ? 'edit' : (in_array($page,['delete_confirm']) ? 'delete' : $page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PeopleDesk — HR Management</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --white:#ffffff;--bg:#f4f3f0;--bg2:#eeecea;
  --sidebar:#1c1917;--sidebar2:#292524;--sidebar3:#3c3836;
  --accent:#c2410c;--accent2:#ea580c;--accent-light:#fff7ed;--accent-border:#fed7aa;
  --blue:#1d4ed8;--blue-l:#eff6ff;--blue-b:#bfdbfe;
  --green:#15803d;--green-l:#f0fdf4;--green-b:#bbf7d0;
  --red:#b91c1c;--red-l:#fef2f2;--red-b:#fecaca;
  --amber:#92400e;--amber-l:#fffbeb;--amber-b:#fde68a;
  --txt:#1c1917;--txt2:#44403c;--txt3:#78716c;--txt4:#a8a29e;
  --border:#e7e5e4;--border2:#d6d3d1;
  --shadow:0 1px 3px rgba(0,0,0,.08),0 1px 2px rgba(0,0,0,.05);
  --shadow2:0 4px 8px rgba(0,0,0,.08),0 2px 4px rgba(0,0,0,.05);
  --shadow3:0 10px 20px rgba(0,0,0,.1),0 4px 8px rgba(0,0,0,.06);
  --fh:'Instrument Serif',serif;--fb:'Instrument Sans',sans-serif;--fm:'JetBrains Mono',monospace;
  --r:10px;
}

body{font-family:var(--fb);background:var(--bg);color:var(--txt);min-height:100vh;display:flex;font-size:16px;line-height:1.6}

/* SIDEBAR */
.sidebar{width:260px;min-width:260px;background:var(--sidebar);display:flex;flex-direction:column;min-height:100vh;position:sticky;top:0;height:100vh;overflow-y:auto}
.sb-brand{padding:28px 22px 22px;border-bottom:1px solid var(--sidebar3)}
.sb-logo{display:flex;align-items:center;gap:12px}
.sb-icon{width:42px;height:42px;background:var(--accent2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sb-icon svg{width:22px;height:22px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.sb-name{font-family:var(--fh);font-size:1.4rem;color:#fafaf9;font-style:italic}
.sb-sub{font-size:.75rem;color:#a8a29e;letter-spacing:.04em;text-transform:uppercase;margin-top:2px}
.sb-section{padding:22px 14px 8px;font-size:.73rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#78716c}
.sb-nav{padding:0 12px;flex:1}
.sb-link{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:8px;text-decoration:none;color:#a8a29e;font-size:1rem;font-weight:500;transition:background .15s,color .15s;margin-bottom:4px}
.sb-link svg{width:18px;height:18px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.sb-link:hover{background:var(--sidebar2);color:#e7e5e4}
.sb-link.active{background:var(--accent);color:#fff}
.sb-link.active svg{stroke:#fff}
.sb-footer{padding:20px 22px;border-top:1px solid var(--sidebar3);margin-top:auto}
.sb-user{display:flex;align-items:center;gap:12px}
.sb-avi{width:40px;height:40px;background:var(--sidebar3);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#d6d3d1;flex-shrink:0}
.sb-uname{font-size:.95rem;color:#d6d3d1;font-weight:600}
.sb-urole{font-size:.78rem;color:#78716c}

/* MAIN */
.main{flex:1;display:flex;flex-direction:column;min-width:0;overflow:hidden}
.topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 40px;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10}
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:.95rem;color:var(--txt3)}
.breadcrumb a{color:var(--txt3);text-decoration:none}
.breadcrumb a:hover{color:var(--txt)}
.breadcrumb .sep{color:var(--txt4)}
.breadcrumb .cur{color:var(--txt);font-weight:600}
.tb-badge{background:var(--accent-light);color:var(--accent);font-size:.8rem;font-weight:600;padding:5px 16px;border-radius:20px;border:1px solid var(--accent-border)}

/* CONTENT */
.content{padding:40px 44px;flex:1}

/* DASHBOARD */
.dash-header{margin-bottom:40px}
.dash-header h1{font-family:var(--fh);font-size:2.6rem;font-weight:400;color:var(--txt);line-height:1.2}
.dash-header h1 em{font-style:italic;color:var(--accent2)}
.dash-header p{color:var(--txt3);margin-top:8px;font-size:1.05rem}

.stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:44px}
.stat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:26px 28px;box-shadow:var(--shadow)}
.stat-label{font-size:.82rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt3);margin-bottom:12px}
.stat-val{font-family:var(--fm);font-size:2.2rem;font-weight:500;color:var(--txt);line-height:1}
.stat-hint{font-size:.85rem;color:var(--txt4);margin-top:8px}

/* ACTION BUTTONS */
.action-label{font-size:.84rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--txt3);margin-bottom:18px}
.action-btn-group{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;max-width:760px}
.action-btn{
  display:flex;align-items:center;justify-content:center;gap:12px;
  padding:20px 32px;border-radius:var(--r);
  font-family:var(--fb);font-size:1.05rem;font-weight:600;
  text-decoration:none;border:1.5px solid transparent;
  transition:all .18s;letter-spacing:.01em;
}
.action-btn svg{width:22px;height:22px;flex-shrink:0;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;fill:none}
.abtn-green{background:var(--green-l);color:var(--green);border-color:var(--green-b)}
.abtn-green:hover{background:#dcfce7;border-color:#4ade80;box-shadow:var(--shadow3);transform:translateY(-2px)}
.abtn-blue{background:var(--blue-l);color:var(--blue);border-color:var(--blue-b)}
.abtn-blue:hover{background:#dbeafe;border-color:#60a5fa;box-shadow:var(--shadow3);transform:translateY(-2px)}
.abtn-red{background:var(--red-l);color:var(--red);border-color:var(--red-b)}
.abtn-red:hover{background:#fee2e2;border-color:#f87171;box-shadow:var(--shadow3);transform:translateY(-2px)}
.abtn-amber{background:var(--amber-l);color:var(--amber);border-color:var(--amber-b)}
.abtn-amber:hover{background:#fef3c7;border-color:#f59e0b;box-shadow:var(--shadow3);transform:translateY(-2px)}

/* PAGE HEADER */
.page-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:30px;gap:16px}
.page-head-left h2{font-family:var(--fh);font-size:2rem;font-weight:400;color:var(--txt)}
.page-head-left p{font-size:1rem;color:var(--txt3);margin-top:5px}

/* FORM CARD */
.form-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--shadow2)}
.fc-head{padding:22px 30px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px}
.fc-dot{width:11px;height:11px;border-radius:50%;flex-shrink:0}
.fc-head-text h3{font-size:1.1rem;font-weight:700;color:var(--txt)}
.fc-head-text p{font-size:.88rem;color:var(--txt3);margin-top:2px}
.fc-body{padding:30px 36px}
.fc-footer{padding:22px 30px;border-top:1px solid var(--border);background:#fafaf9;border-radius:0 0 var(--r) var(--r);display:flex;align-items:center;justify-content:flex-end;gap:14px}

/* FORM FIELDS */
.fsec{font-size:.78rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--txt4);margin:26px 0 18px;padding-bottom:8px;border-bottom:1px solid var(--border)}
.fsec:first-child{margin-top:0}
.frow{display:flex;gap:20px}
.fg{flex:1;margin-bottom:20px}
.fg.full{flex:100%}
label{display:block;font-size:.92rem;font-weight:600;color:var(--txt2);margin-bottom:8px}
.req{color:var(--accent2);margin-left:2px}

input[type=text],input[type=email],input[type=number],input[type=date],select,textarea{
  width:100%;border:1.5px solid var(--border2);border-radius:8px;
  background:var(--white);color:var(--txt);
  font-family:var(--fb);font-size:1rem;padding:12px 15px;
  outline:none;transition:border-color .15s,box-shadow .15s;
}
input::placeholder,textarea::placeholder{color:var(--txt4)}
input:focus,select:focus,textarea:focus{border-color:#93c5fd;box-shadow:0 0 0 3px rgba(59,130,246,.13)}
select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2378716c'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;background-size:18px;padding-right:40px}
textarea{resize:vertical;min-height:96px;line-height:1.6}
.field-locked input{background:#fafaf9;color:var(--txt3);cursor:not-allowed;border-style:dashed}
.locked-tag{display:inline-flex;align-items:center;gap:4px;font-size:.74rem;color:var(--txt4);background:var(--bg);border:1px solid var(--border);padding:2px 10px;border-radius:20px;margin-left:8px;font-weight:500}
.locked-tag svg{width:10px;height:10px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 24px;border-radius:8px;font-family:var(--fb);font-size:1rem;font-weight:600;cursor:pointer;border:none;transition:all .15s;text-decoration:none;white-space:nowrap}
.btn svg{width:17px;height:17px;flex-shrink:0}
.btn-primary{background:var(--accent);color:#fff;box-shadow:0 1px 3px rgba(0,0,0,.12)}
.btn-primary:hover{background:#b43c0b;box-shadow:var(--shadow2)}
.btn-blue{background:var(--blue);color:#fff;box-shadow:0 1px 3px rgba(0,0,0,.12)}
.btn-blue:hover{background:#1e40af}
.btn-danger{background:var(--red);color:#fff;box-shadow:0 1px 3px rgba(0,0,0,.12)}
.btn-danger:hover{background:#991b1b}
.btn-ghost{background:var(--white);color:var(--txt2);border:1.5px solid var(--border2);box-shadow:0 1px 2px rgba(0,0,0,.05)}
.btn-ghost:hover{background:var(--bg)}
.btn-sm{padding:9px 18px;font-size:.88rem}
.btn-lg{padding:14px 32px;font-size:1.05rem}

/* ALERTS */
.alert{display:flex;align-items:flex-start;gap:12px;padding:16px 20px;border-radius:var(--r);font-size:1rem;margin-bottom:26px;border:1px solid}
.alert svg{width:20px;height:20px;flex-shrink:0;margin-top:1px;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;fill:none}
.al-success{background:var(--green-l);border-color:var(--green-b);color:var(--green)}
.al-error{background:var(--red-l);border-color:var(--red-b);color:var(--red)}
.al-info{background:var(--blue-l);border-color:var(--blue-b);color:var(--blue)}

/* LOOKUP */
.lookup-panel{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:28px 32px;margin-bottom:26px;box-shadow:var(--shadow)}
.lookup-panel h3{font-size:1.05rem;font-weight:700;color:var(--txt);margin-bottom:18px;display:flex;align-items:center;gap:10px}
.lookup-panel h3 svg{width:18px;height:18px;stroke:var(--txt3);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.lookup-inline{display:flex;gap:14px;align-items:flex-end}
.lookup-inline .fg{margin-bottom:0;min-width:320px}

/* CONFIRM */
.confirm-panel{background:var(--red-l);border:1px solid var(--red-b);border-radius:var(--r);padding:26px 28px;margin-bottom:24px}
.confirm-panel h3{color:var(--red);font-size:1.05rem;font-weight:700;display:flex;align-items:center;gap:10px;margin-bottom:10px}
.confirm-panel h3 svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.confirm-panel>p{font-size:.95rem;color:#7f1d1d;margin-bottom:20px}
.emp-detail-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px 24px;background:var(--white);border:1px solid var(--red-b);border-radius:8px;padding:20px 22px;margin-bottom:20px}
.edg-item dt{font-size:.76rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt4);margin-bottom:4px}
.edg-item dd{font-size:1rem;color:var(--txt);font-weight:600}
.confirm-actions{display:flex;gap:14px}

/* TABLE */
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead tr{border-bottom:2px solid var(--border)}
thead th{padding:14px 18px;text-align:left;font-size:.8rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt3);white-space:nowrap;background:var(--bg)}
tbody tr{border-bottom:1px solid var(--border);transition:background .12s}
tbody tr:hover{background:#fafaf9}
tbody tr:last-child{border-bottom:none}
tbody td{padding:15px 18px;font-size:.97rem;color:var(--txt2);white-space:nowrap}
.eid{font-family:var(--fm);font-size:.84rem;color:var(--blue);background:var(--blue-l);padding:3px 10px;border-radius:5px;font-weight:500}
.name-cell{display:flex;align-items:center;gap:12px}
.av{width:36px;height:36px;border-radius:50%;background:var(--accent-light);border:1px solid var(--accent-border);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:var(--accent);flex-shrink:0}
.nm{font-weight:600;color:var(--txt);font-size:.97rem}
.em{color:var(--txt3);font-size:.9rem}
.dept{background:var(--bg2);color:var(--txt2);font-size:.82rem;font-weight:600;padding:3px 10px;border-radius:5px;border:1px solid var(--border)}
.sal{font-family:var(--fm);font-size:.93rem;font-weight:500;color:var(--green)}

@media(max-width:960px){
  .sidebar{display:none}
  .content{padding:24px 20px}
  .frow{flex-direction:column;gap:0}
  .action-btn-group{grid-template-columns:1fr}
  .stats-row{grid-template-columns:1fr 1fr}
  .emp-detail-grid{grid-template-columns:1fr 1fr}
  .lookup-inline{flex-direction:column}
  .confirm-actions{flex-direction:column}
}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-icon">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div>
        <div class="sb-name">PeopleDesk</div>
        <div class="sb-sub">HR Management</div>
      </div>
    </div>
  </div>
  <div class="sb-nav">
    <div class="sb-section">Main Menu</div>
    <?php foreach($navItems as $key => $item): ?>
    <a href="index.php?page=<?=$key?>" class="sb-link <?=$activePage===$key?'active':''?>">
      <svg viewBox="0 0 24 24"><path d="<?=$item['icon']?>"/></svg>
      <?=$item['label']?>
    </a>
    <?php endforeach; ?>
  </div>
  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avi">HR</div>
      <div>
        <div class="sb-uname">HR Administrator</div>
        <div class="sb-urole">Full Access</div>
      </div>
    </div>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <nav class="breadcrumb">
      <a href="index.php">PeopleDesk</a>
      <span class="sep">/</span>
      <?php
      $labels=['home'=>'Dashboard','add'=>'Add Employee','edit'=>'Edit Employee','edit_form'=>'Edit Employee','delete'=>'Remove Employee','delete_confirm'=>'Remove Employee','display'=>'All Employees'];
      echo "<span class='cur'>".($labels[$page]??'Dashboard')."</span>";
      ?>
    </nav>
    <span class="tb-badge">v2.0 Live</span>
  </div>

  <div class="content">

<?php if($page==='home'): ?>
<div class="dash-header">
  <h1>Good morning, <em>HR Admin</em></h1>
  <p>Manage your employee records — add, edit, remove or browse the full directory.</p>
</div>

<div class="stats-row">
  <div class="stat-card">
    <div class="stat-label">Total Employees</div>
    <div class="stat-val"><?php $r=$conn->query("SELECT COUNT(*) as c FROM employees");$c=$r?$r->fetch_assoc()['c']:0;echo $c;?></div>
    <div class="stat-hint">Records in system</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Departments</div>
    <div class="stat-val"><?php $r=$conn->query("SELECT COUNT(DISTINCT department) as c FROM employees");$c=$r?$r->fetch_assoc()['c']:0;echo $c;?></div>
    <div class="stat-hint">Active departments</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Database Status</div>
    <div class="stat-val" style="font-size:1.15rem;color:#15803d;margin-top:8px">● Connected</div>
    <div class="stat-hint"><?=$dbname?> · port <?=$port?></div>
  </div>
</div>

<div class="action-label">Quick Actions</div>
<div class="action-btn-group">
  <a href="index.php?page=add" class="action-btn abtn-green">
    <svg viewBox="0 0 24 24" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
    Add New Employee
  </a>
  <a href="index.php?page=edit" class="action-btn abtn-blue">
    <svg viewBox="0 0 24 24" stroke="currentColor"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    Edit Employee
  </a>
  <a href="index.php?page=delete" class="action-btn abtn-red">
    <svg viewBox="0 0 24 24" stroke="currentColor"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
    Delete Employee
  </a>
  <a href="index.php?page=display" class="action-btn abtn-amber">
    <svg viewBox="0 0 24 24" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    Display All Employees
  </a>
</div>

<?php elseif($page==='add'): ?>
<div class="page-head">
  <div class="page-head-left">
    <h2>Add New Employee</h2>
    <p>Complete all required fields marked with <span style="color:var(--accent2)">*</span> to register a new employee.</p>
  </div>
  <a href="index.php" class="btn btn-ghost btn-sm">← Back</a>
</div>
<?php if($message): ?>
<div class="alert <?=$messageType==='success'?'al-success':($messageType==='error'?'al-error':'al-info')?>">
  <svg viewBox="0 0 24 24"><?=$messageType==='success'?'<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>':($messageType==='error'?'<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>':'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>')?></svg>
  <span><?=$message?></span>
</div>
<?php endif; ?>
<div class="form-card">
  <div class="fc-head">
    <div class="fc-dot" style="background:#15803d"></div>
    <div class="fc-head-text"><h3>Employee Information</h3><p>Fill in personal, contact and employment details below</p></div>
  </div>
  <form method="POST" action="index.php?page=add" autocomplete="off">
  <div class="fc-body">
    <div class="fsec">Personal Information</div>
    <div class="fg" style="max-width:380px"><label>Employee ID <span class="req">*</span></label><input type="text" name="employee_id" placeholder="e.g. EMP-001" required></div>
    <div class="frow">
      <div class="fg"><label>First Name <span class="req">*</span></label><input type="text" name="first_name" placeholder="John" required></div>
      <div class="fg"><label>Last Name <span class="req">*</span></label><input type="text" name="last_name" placeholder="Smith" required></div>
    </div>
    <div class="frow">
      <div class="fg"><label>Gender</label><select name="gender"><option value="">— Select —</option><option>Male</option><option>Female</option><option>Other</option><option value="Prefer not to say">Prefer not to say</option></select></div>
      <div class="fg"><label>Date of Birth</label><input type="date" name="dob"></div>
      <div class="fg"><label>Age</label><input type="number" name="age" placeholder="28" min="18" max="100"></div>
    </div>
    <div class="fsec">Contact Details</div>
    <div class="frow">
      <div class="fg"><label>Email Address</label><input type="email" name="email" placeholder="john@company.com"></div>
      <div class="fg"><label>Phone Number</label><input type="text" name="phone" placeholder="+91 98765 43210"></div>
    </div>
    <div class="fg full"><label>Street Address</label><textarea name="address" placeholder="Building, street, area…"></textarea></div>
    <div class="frow">
      <div class="fg"><label>Pincode</label><input type="text" name="pincode" placeholder="400001"></div>
      <div class="fg"><label>State</label><input type="text" name="state" placeholder="Maharashtra"></div>
      <div class="fg"><label>Country</label><input type="text" name="country" placeholder="India"></div>
    </div>
    <div class="fsec">Employment Details</div>
    <div class="frow">
      <div class="fg"><label>Department</label><input type="text" name="department" placeholder="Engineering"></div>
      <div class="fg"><label>Role / Designation</label><input type="text" name="role" placeholder="Senior Developer"></div>
    </div>
    <div class="fg" style="max-width:340px"><label>Annual Salary (₹)</label><input type="number" name="salary" placeholder="750000" min="0"></div>
  </div>
  <div class="fc-footer">
    <a href="index.php" class="btn btn-ghost">Discard</a>
    <button type="submit" name="do_add" class="btn btn-primary btn-lg">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Employee
    </button>
  </div>
  </form>
</div>

<?php elseif($page==='edit'): ?>
<div class="page-head">
  <div class="page-head-left">
    <h2>Edit Employee</h2>
    <p>Search by Employee ID to load the record for editing.</p>
  </div>
  <a href="index.php" class="btn btn-ghost btn-sm">← Back</a>
</div>
<?php if($message): ?>
<div class="alert <?=$messageType==='error'?'al-error':'al-info'?>">
  <svg viewBox="0 0 24 24"><?=$messageType==='error'?'<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>':'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'?></svg>
  <span><?=$message?></span>
</div>
<?php endif; ?>
<div class="lookup-panel">
  <h3><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>Find Employee Record</h3>
  <form method="POST" action="index.php?page=edit" autocomplete="off">
    <div class="lookup-inline">
      <div class="fg"><label>Employee ID</label><input type="text" name="lookup_id" placeholder="Enter Employee ID, e.g. EMP-001" required></div>
      <button type="submit" name="do_edit_lookup" class="btn btn-blue btn-lg">Search Record</button>
    </div>
  </form>
</div>

<?php elseif($page==='edit_form'): $p=$prefill; ?>
<div class="page-head">
  <div class="page-head-left">
    <h2>Editing — <?=h($p['first_name']).' '.h($p['last_name'])?></h2>
    <p>Employee ID and name are fixed. All other fields can be updated.</p>
  </div>
  <a href="index.php?page=edit" class="btn btn-ghost btn-sm">← Back to Search</a>
</div>
<?php if($message): ?>
<div class="alert <?=$messageType==='success'?'al-success':'al-error'?>">
  <svg viewBox="0 0 24 24"><?=$messageType==='success'?'<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>':'<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'?></svg>
  <span><?=$message?></span>
</div>
<?php endif; ?>
<div class="form-card">
  <div class="fc-head">
    <div class="fc-dot" style="background:#1d4ed8"></div>
    <div class="fc-head-text"><h3>Update Employee Record</h3><p>ID: <?=h($p['employee_id'])?> · Modifying existing record</p></div>
  </div>
  <form method="POST" action="index.php?page=edit_form" autocomplete="off">
  <input type="hidden" name="employee_id" value="<?=h($p['employee_id'])?>">
  <input type="hidden" name="first_name" value="<?=h($p['first_name'])?>">
  <input type="hidden" name="last_name" value="<?=h($p['last_name'])?>">
  <div class="fc-body">
    <div class="fsec">Personal Information</div>
    <div class="fg field-locked" style="max-width:380px">
      <label>Employee ID <span class="locked-tag"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Locked</span></label>
      <input type="text" value="<?=h($p['employee_id'])?>" readonly>
    </div>
    <div class="frow">
      <div class="fg field-locked">
        <label>First Name <span class="locked-tag"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Locked</span></label>
        <input type="text" value="<?=h($p['first_name'])?>" readonly>
      </div>
      <div class="fg field-locked">
        <label>Last Name <span class="locked-tag"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Locked</span></label>
        <input type="text" value="<?=h($p['last_name'])?>" readonly>
      </div>
    </div>
    <div class="frow">
      <div class="fg"><label>Gender</label>
        <select name="gender"><option value="">— Select —</option>
          <?php foreach(['Male','Female','Other','Prefer not to say'] as $g): ?>
          <option value="<?=$g?>" <?=$p['gender']==$g?'selected':''?>><?=$g?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="fg"><label>Date of Birth</label><input type="date" name="dob" value="<?=h($p['dob'])?>"></div>
      <div class="fg"><label>Age</label><input type="number" name="age" value="<?=h($p['age'])?>" min="18" max="100"></div>
    </div>
    <div class="fsec">Contact Details</div>
    <div class="frow">
      <div class="fg"><label>Email Address</label><input type="email" name="email" value="<?=h($p['email'])?>"></div>
      <div class="fg"><label>Phone Number</label><input type="text" name="phone" value="<?=h($p['phone'])?>"></div>
    </div>
    <div class="fg full"><label>Street Address</label><textarea name="address"><?=h($p['address'])?></textarea></div>
    <div class="frow">
      <div class="fg"><label>Pincode</label><input type="text" name="pincode" value="<?=h($p['pincode'])?>"></div>
      <div class="fg"><label>State</label><input type="text" name="state" value="<?=h($p['state'])?>"></div>
      <div class="fg"><label>Country</label><input type="text" name="country" value="<?=h($p['country'])?>"></div>
    </div>
    <div class="fsec">Employment Details</div>
    <div class="frow">
      <div class="fg"><label>Department</label><input type="text" name="department" value="<?=h($p['department'])?>"></div>
      <div class="fg"><label>Role / Designation</label><input type="text" name="role" value="<?=h($p['role'])?>"></div>
    </div>
    <div class="fg" style="max-width:340px"><label>Annual Salary (₹)</label><input type="number" name="salary" value="<?=h($p['salary'])?>" min="0"></div>
  </div>
  <div class="fc-footer">
    <a href="index.php?page=edit" class="btn btn-ghost">Cancel</a>
    <button type="submit" name="do_edit_save" class="btn btn-blue btn-lg">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      Save Changes
    </button>
  </div>
  </form>
</div>

<?php elseif($page==='delete'): ?>
<div class="page-head">
  <div class="page-head-left">
    <h2>Remove Employee</h2>
    <p>Search by Employee ID to load the record for permanent deletion.</p>
  </div>
  <a href="index.php" class="btn btn-ghost btn-sm">← Back</a>
</div>
<?php if($message): ?>
<div class="alert <?=$messageType==='success'?'al-success':($messageType==='error'?'al-error':'al-info')?>">
  <svg viewBox="0 0 24 24"><?=$messageType==='success'?'<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>':'<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'?></svg>
  <span><?=$message?></span>
</div>
<?php endif; ?>
<div class="lookup-panel" style="border-color:var(--red-b)">
  <h3><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>Find Employee to Remove</h3>
  <form method="POST" action="index.php?page=delete" autocomplete="off">
    <div class="lookup-inline">
      <div class="fg"><label>Employee ID</label><input type="text" name="lookup_id" placeholder="Enter Employee ID, e.g. EMP-001" required></div>
      <button type="submit" name="do_delete_lookup" class="btn btn-danger btn-lg">Find Record</button>
    </div>
  </form>
</div>

<?php elseif($page==='delete_confirm'): $p=$prefill; ?>
<div class="page-head">
  <div class="page-head-left">
    <h2>Confirm Deletion</h2>
    <p>Review the employee details below before proceeding.</p>
  </div>
  <a href="index.php?page=delete" class="btn btn-ghost btn-sm">← Back</a>
</div>
<div class="confirm-panel">
  <h3>
    <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    This action cannot be undone
  </h3>
  <p>You are about to permanently delete the following employee record from the database.</p>
  <div class="emp-detail-grid">
    <dl class="edg-item"><dt>Employee ID</dt><dd><?=h($p['employee_id'])?></dd></dl>
    <dl class="edg-item"><dt>Full Name</dt><dd><?=h($p['first_name']).' '.h($p['last_name'])?></dd></dl>
    <dl class="edg-item"><dt>Gender</dt><dd><?=h($p['gender'])?></dd></dl>
    <dl class="edg-item"><dt>Department</dt><dd><?=h($p['department'])?></dd></dl>
    <dl class="edg-item"><dt>Role</dt><dd><?=h($p['role'])?></dd></dl>
    <dl class="edg-item"><dt>Annual Salary</dt><dd>&#8377;<?=number_format($p['salary'],2)?></dd></dl>
  </div>
  <form method="POST" action="index.php?page=delete_confirm">
    <input type="hidden" name="employee_id" value="<?=h($p['employee_id'])?>">
    <div class="confirm-actions">
      <button type="submit" name="do_delete_confirm" class="btn btn-danger btn-lg">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
        Delete Permanently
      </button>
      <a href="index.php?page=delete" class="btn btn-ghost btn-lg">Cancel</a>
    </div>
  </form>
</div>

<?php elseif($page==='display'): ?>
<div class="page-head">
  <div class="page-head-left">
    <h2>Employee Directory</h2>
    <p>All active employee records sorted by ID.</p>
  </div>
  <a href="index.php?page=add" class="btn btn-primary">+ Add Employee</a>
</div>
<?php if($message): ?>
<div class="alert <?=$messageType==='success'?'al-success':($messageType==='error'?'al-error':'al-info')?>">
  <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <span><?=$message?></span>
</div>
<?php endif; ?>
<div class="form-card">
  <div class="fc-head">
    <div class="fc-dot" style="background:#92400e"></div>
    <div class="fc-head-text"><h3>All Employees</h3><p>Complete list of registered employees</p></div>
  </div>
  <div class="table-wrap"><?=$tableHTML?></div>
</div>

<?php endif; ?>

  </div>
</div>
</body>
</html>
 