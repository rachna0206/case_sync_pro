<?php
include "header.php";

if (isset($_COOKIE['edit_id']) || isset($_COOKIE['view_id'])) {
    $mode = (isset($_COOKIE['edit_id'])) ? 'edit' : 'view';
    $Id = (isset($_COOKIE['edit_id'])) ? $_COOKIE['edit_id'] : $_COOKIE['view_id'];
    $stmt = $obj->con1->prepare("SELECT * FROM `task` WHERE id=?");
    $stmt->bind_param('i', $Id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (isset($_REQUEST["save"])) {
    $cid = $_REQUEST['case_number_id'];
    $ato = $_REQUEST['alloted_to'];
    $instruction =  $_REQUEST['instruction'];
    $action_by = "advocate";
    $adate = $_REQUEST['alloted_date'];
    $edate = $_REQUEST['exp_end_date'];
    $status = $_REQUEST['radio'];

    $sessionID = $_SESSION["id"];
    $noti_status = 1;
    $play_status = 1;
    $noti_type = "task_assigned";
    $sender_type = "advocate";
   
    $receiver_type="intern";
    $noti_msg = "New task has been assigned";

    try {
        // echo "INSERT INTO `task`(`case_id`, `alloted_to`,`instruction` , `alloted_by` ,`action_by`,`alloted_date`,`expected_end_date`, `status`) VALUES ($cid, $ato, $instruction, $sessionID, $action_by, $adate, $edate, $status)";
        $stmt = $obj->con1->prepare("INSERT INTO `task`(`case_id`, `alloted_to`,`instruction` , `alloted_by` ,`action_by`,`alloted_date`,`expected_end_date`, `status`) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ississss", $cid, $ato, $instruction, $sessionID, $action_by, $adate, $edate, $status);
        $Resp = $stmt->execute();
        $last_id=mysqli_insert_id($obj->con1);
        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {

     
         //add notification
         $stmt_noti = $obj->con1->prepare("INSERT INTO `notification` (`task_id`, `type`, `sender_id`,`receiver_id`, `msg`, `sender_type`,`receiver_type`, `status`, `playstatus`) VALUES (?, ?, ?, ?, ?, ?, ?,?,?)");

         $stmt_noti->bind_param("isiisssii", $last_id, $noti_type, $sessionID,$ato, $noti_msg, $sender_type,$receiver_type, $noti_status, $play_status);
         $Resp_noti = $stmt_noti->execute();
         $stmt_noti->close();


        setcookie("msg", "data", time() + 3600, "/");
        header("location:task.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:task.php");
    }
}

if (isset($_REQUEST["btn_city"])) {

    $state = $_REQUEST['state_id'];
    $city_name = $_REQUEST['name'];
    $status = 'enable';
    try {
        // echo "INSERT INTO `city`(`name`, `status`) VALUES (". $city_name.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `city`(`state_id`,`name`, `status`) VALUES (?,?,?)");
        $stmt->bind_param("iss", $state, $city_name, $status);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }
    if ($Resp) {


        header("location:task_add.php");
    } else {

        header("location:task_add.php");
    }
}

if (isset($_REQUEST["btn_intern"])) {

    $int_name = $_REQUEST['int_name'];
    $contact_no = $_REQUEST['contact'];
    $email = $_REQUEST['email'];
    $password = $_REQUEST['password'];
    $date = $_REQUEST['date'];
    $status = 'enable';
    try {
        // echo "INSERT INTO `city`(`name`, `status`) VALUES (". $city_name.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `interns`(`name`,`contact`,`email`,`password`, `date_time`,`status`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $int_name,  $contact_no, $email, $password, $date, $status);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }
    if ($Resp) {


        header("location:task_add.php");
    } else {

        header("location:task_add.php");
    }
}

if (isset($_REQUEST["btn_case_type"])) {

    $case_type_m = $_REQUEST['c_type'];
    $status = 'enable';
    try {
        // echo "INSERT INTO `city`(`case_type`, `status`) VALUES (". $case_type_m.", ".$status.")";
        $stmt = $obj->con1->prepare("INSERT INTO `case_type`(`case_type`, `status`) VALUES (?,?)");
        $stmt->bind_param("ss", $case_type_m, $status);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception(
                "Problem in adding! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }
    if ($Resp) {


        header("location:task_add.php");
    } else {

        header("location:task_add.php");
    }
}



if (isset($_REQUEST["update"])) {
    $e_id = $_COOKIE['edit_id'];
    $cid = $_REQUEST['case_number_id'];
    $ato = $_REQUEST['alloted_to'];
    $adate = $_REQUEST['alloted_date'];
    $edate = $_REQUEST['exp_end_date'];
    $status = $_REQUEST['radio'];
    $instruction =  $_REQUEST['instruction'];


    try {
        $stmt = $obj->con1->prepare("UPDATE `task` SET `case_id`=?, `alloted_to`=?,`instruction`=?,`alloted_date`=?,`expected_end_date`=?,`status`=? WHERE `id`=?");
        $stmt->bind_param("isssssi",  $cid, $ato, $instruction, $adate, $edate, $status, $e_id);
        $Resp = $stmt->execute();
        if (!$Resp) {
            throw new Exception(
                "Problem in updating! " . strtok($obj->con1->error, "(")
            );
        }
        $stmt->close();
    } catch (\Exception $e) {
        setcookie("sql_error", urlencode($e->getMessage()), time() + 3600, "/");
    }

    if ($Resp) {
        setcookie("edit_id", "", time() - 3600, "/");
        setcookie("msg", "update", time() + 3600, "/");
        header("location:task.php");
    } else {
        setcookie("msg", "fail", time() + 3600, "/");
        header("location:task.php");
    }
}
?>
<!-- <a href="javascript:go_back();"><i class="bi bi-arrow-left"></i></a> -->
<div class="pagetitle">
    <h1>Task</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item">Task</li>
            <li class="breadcrumb-item active">
                <?php echo (isset($mode)) ? (($mode == 'view') ? 'View' : 'Edit') : 'Add' ?>- Data</li>
        </ol>
    </nav>
</div><!-- End Page Title -->
<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">

                    <form class="row g-3 pt-3" method="post" enctype="multipart/form-data">
                        <div class="row pt-3 align-items-end">
                            <!-- Case Type Dropdown and Button -->
                            <div class="col-md-6">
                                <label for="case_type" class="form-label">Case Type</label>
                                <div class="d-flex">
                                    <select class="form-select me-2" id="case_type" name="case_type"
                                        <?= isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                        <option value="">Select Case Type</option>
                                        <?php
                                        $comp = "SELECT * FROM `case_type` WHERE status='enable' and id!=0";
                                        $result = $obj->select($comp);
                                        $selectedcourtId = isset($data['case_type']) ? $data['case_type'] : '';

                                        while ($row = mysqli_fetch_array($result)) {
                                            $selected = ($row["id"] == $selectedcourtId) ? 'selected' : '';
                                        ?>
                                            <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($row["case_type"]) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <!-- City Dropdown and Button -->
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <div class="d-flex">
                                    <select class="form-select me-2" id="city" name="city" onblur="filterTask()"
                                        <?= isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                        <option value="">Select City</option>
                                        <?php
                                        $task = "SELECT * FROM `city` WHERE status='enable'";
                                        $result = $obj->select($task);
                                        $selectedCaseId = isset($data['city_id']) ? $data['city_id'] : '';

                                        while ($row = mysqli_fetch_array($result)) {
                                            $selected = ($row["id"] == $selectedCaseId) ? 'selected' : '';
                                        ?>
                                            <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($row["name"]) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <span style="color:red" class="d-none" id="err"></span>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="col-md-12 pt-3">
                        <label for="case_id" class="form-label">Case Number <span id="updatedMsg" style="font-style: italic; color: green; display: none;">( Updated )</span></label>

                        <select class="form-select" id="case_number_id" name="case_number_id"
                            <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?> required>
                            <option value="">Select a Case</option>
                            <?php
                            if (isset($mode)) {
                                $task = "SELECT id, case_no FROM `case`";
                                $result = $obj->select($task);
                                $selectedCaseId = isset($data['case_id']) ? $data['case_id'] : '';

                                while ($row = mysqli_fetch_array($result)) {
                                    $selected = ($row["id"] == $selectedCaseId) ? 'selected' : '';
                            ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($row["case_no"]) ?>
                                    </option>
                            <?php }
                            } ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label for="alloted_to_id" class="form-label">Alloted To</label>
                        <div class="d-flex">
                            <select class="form-select" id="alloted_to" name="alloted_to"
                                <?php echo isset($mode) && $mode === 'view' ? 'disabled' : '' ?>>
                                <option value="">Select Intern</option>
                                <?php
                                $task = "SELECT * FROM `interns`";
                                $result = $obj->select($task);
                                $selectedCaseId = isset($data['alloted_to']) ? $data['alloted_to'] : '';

                                while ($row = mysqli_fetch_array($result)) {
                                    $selected = ($row["id"] == $selectedCaseId) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($row["id"]) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($row["name"]) ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal"
                                data-bs-target="#addintrnmodal">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>


                    <div class="col-md-12">
                        <label for="inputPassword" class="col-sm-2 col-form-label">Task Instruction</label>
                        <textarea class="form-control" style="height: 100px" id="instruction" name="instruction"
                            required
                            <?php echo isset($mode) && $mode == 'view' ? 'readonly' : '' ?>><?php echo (isset($mode)) ? $data['instruction'] : '' ?></textarea>
                    </div>
                    <div class="row pt-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label">Allotted Date</label>
                            <input type="date" class="form-control" id="alloted_date" name="alloted_date"
                                value="<?php echo (isset($mode) && isset($data['alloted_date']) && !empty($data['alloted_date'])) ? date('Y-m-d', strtotime($data['alloted_date'])) : date('Y-m-d'); ?>"
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                        </div>

                        <div class="col-md-6">
                            <label for="title" class="form-label">Expected End Date</label>
                            <input type="date" class="form-control" id="exp_end_date" name="exp_end_date"
                                value="<?php echo (isset($mode) && isset($data['expected_end_date']) && !empty($data['expected_end_date'])) ? date('Y-m-d', strtotime($data['alloted_date'])) : date('Y-m-d'); ?>"
                                <?php echo isset($mode) && $mode == 'view' ? 'readonly' : ''; ?>>
                        </div>
                    </div>
                    <input type="hidden" name="radio" value="allotted">
                    <div class="text-left mt-4">
                        <button type="submit"
                            name="<?php echo isset($mode) && $mode == 'edit' ? 'update' : 'save' ?>" id="save"
                            class="btn btn-success <?php echo isset($mode) && $mode == 'view' ? 'd-none' : '' ?>"><?php echo isset($mode) && $mode == 'edit' ? 'Update' : 'Save' ?>
                        </button>
                        <button type="button" class="btn btn-danger" onclick="javascript: go_back();">
                            Close</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="addintrnmodal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Interns</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">

                    <div class="col-md-12">
                        <label for="title" class="form-label">Name</label>
                        <input type="text" class="form-control" id="int_name" name="int_name" required>
                    </div>
                    <div class="col-md-12">
                        <label for="title" class="form-label">Contact No.</label>
                        <input type="text" class="form-control" id="contact" name="contact" required>
                    </div>
                    <div class="col-md-12">
                        <label for="title" class="form-label">Email</label>
                        <input type="text" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-12">
                        <label for="title" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="col-md-12">
                        <label for="Date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="add_alloted_to()">Save</button>
            </div>
            </form>
        </div>
    </div>
</div><!-- End add intern Modal-->

<script>
    function go_back() {
        eraseCookie("edit_id");
        eraseCookie("view_id");
        window.location = "task.php";
    }

    function filterTask() {

        var case_type_id = document.getElementById("case_type").value;
        var city_id = document.getElementById("city").value;

        if (case_type_id == "") {
            $("#err").removeClass("d-none").text("Please select a case type.");
        } else if (city_id == "") {
            $("#err").removeClass("d-none").text("Please select a city.");
        } else {
            $("#err").addClass("d-none");
            $.ajax({
                async: true,
                type: "POST",
                url: "action.php?action=filter_case",
                data: "case_type_id=" + case_type_id + "&city_id=" + city_id,
                cache: false,
                success: function(result) {
                    $("#case_number_id").html('<option value="">Select a Case</option>');

                    $("#case_number_id").append(result);

                    var updateMessage = document.getElementById("updatedMsg");
                    updateMessage.style.display = 'inline';
                    setTimeout(function() {
                        updateMessage.style.display = 'none';
                    }, 2000);
                }
            });
        }
    }


    function add_alloted_to() {

        var int_name = document.getElementById("int_name").value;
        var contact = document.getElementById("contact").value;
        var email = document.getElementById("email").value;
        var password = document.getElementById("password").value;
        var date = document.getElementById("date").value;

        $("#addintrnmodal").modal("toggle");
        $.ajax({
            async: true,
            type: "POST",
            url: "action.php?action=add_alloted_to",
            data: "int_name=" + int_name + "&contact=" + contact + "&email=" + email + "&password=" + password + "&date=" + date,
            cache: false,
            success: function(result) {
                $("#alloted_to").append(result);
            }
        });


    }
</script>
<?php
include "footer.php";
?>