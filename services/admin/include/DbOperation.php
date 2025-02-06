<?php
date_default_timezone_set("Asia/Kolkata");
class DbOperation
{
    private $con;

    function __construct()
    {
        require_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect();
        $this->con = $db->connect();
    }

    public function loginAdvocate($user_id, $password)
    {
        $stmt_login = $this->con->prepare("SELECT * FROM `advocate` WHERE `email`=? AND BINARY `password`=? AND status = 'enable'");
        $stmt_login->bind_param("ss", $user_id, $password);
        $stmt_login->execute();
        $result = $stmt_login->get_result();
        $stmt_login->close();
        return $result;
    }

    public function get_case_remarks($case_id) // added by jay 22-01-2025
    {
        $stmt = $this->con->prepare("SELECT c1.id,c1.case_no,case_hist.remarks,case_hist.status, date_format(`case_hist`.dos,'%d-%m-%Y') as fdos , date_format(`case_hist`.date_time,'%d-%m-%Y') as fdt , interns.name as intern_name ,stage.stage as stage_name , c1.case_no,advocate.name as advocate_name from `case_hist` inner join `task` on task.id = case_hist.task_id inner join `case` c1 on c1.id = task.case_id inner join `stage` on case_hist.stage = stage.id inner join `interns` on task.alloted_to = interns.id inner join advocate on advocate.id = task.alloted_by where task.case_id = ? order by case_hist.id DESC");
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function addNewAdvocate($name, $contact, $email, $password)
    {

        $stmt = $this->con->prepare("SELECT contact from advocate where contact = ? ");
        $stmt->bind_param("s", $contact);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 2;
        }


        $stmt = $this->con->prepare("SELECT email from advocate where email = ? ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 3;
        }


        $status = "enable";

        $stmt = $this->con->prepare("INSERT INTO `advocate`(`name`, `contact`, `email`, `status`, `password`) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $name, $contact, $email, $status, $password);
        $result = $stmt->execute();
        $stmt->close();
        return $result;

    }

    public function get_case_counter()
    {
        $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.applicant, a.opp_name, a.sr_date, b.name as court_name,c.case_type, d.name as city_name, e.name as handle_by,DATEDIFF(CURRENT_DATE, a.sr_date) as case_counter FROM `case` a JOIN `court` b ON a.court_name = b.id JOIN `case_type` c ON a.case_type = c.id JOIN `city` d ON a.city_id = d.id JOIN `advocate` e ON a.handle_by = e.id WHERE DATEDIFF(CURRENT_DATE, a.sr_date) < 10");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $stmt = $this->con->prepare("SELECT n1.*, a1.name FROM `notification` n1, advocate a1 WHERE n1.sender_id = a1.id AND n1.status = '1' AND n1.receiver_type = 'advocate'  ORDER BY n1.id DESc");
        $stmt->execute();
        $notification = $stmt->get_result();
        $stmt->close();

        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `case` a WHERE a.id NOT IN (SELECT DISTINCT(case_id) FROM task)");
        $stmt->execute();
        $unassigned_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `case` a WHERE a.id IN (SELECT DISTINCT(case_id) FROM task)");
        $stmt->execute();
        $assigned_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM `case`");
        $stmt->execute();
        $history_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM advocate");
        $stmt->execute();
        $advocate_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM interns");
        $stmt->execute();
        $intern_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM company");
        $stmt->execute();
        $company_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        $stmt = $this->con->prepare("SELECT COUNT(*) as count FROM task t JOIN `case` c ON c.id = t.case_id");
        $stmt->execute();
        $task_count = $stmt->get_result()->fetch_assoc()["count"];
        $stmt->close();

        return [$result,$notification, $unassigned_count, $assigned_count, $history_count, $advocate_count, $intern_count, $company_count, $task_count];

    }


    public function addNewIntern($name, $contact, $email, $password, $start_date)
    {
        $stmt = $this->con->prepare("SELECT contact from interns where contact = ? ");
        $stmt->bind_param("s", $contact);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 2;
        }


        $stmt = $this->con->prepare("SELECT email from interns where email = ? ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if (mysqli_num_rows($result) > 0) {
            return 3;
        }


        $status = "enable";

        $stmt = $this->con->prepare("INSERT INTO `interns`(`name`, `contact`, `email`,`status`, `password`,`date_time`) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $name, $contact, $email, $status, $password, $start_date);
        $result = $stmt->execute();
        $stmt->close();
        return $result;

    }
    public function get_case_type_list()
    {
        $stmt = $this->con->prepare("SELECT `id`,`case_type` FROM `case_type` where `status`='enable' order by id desc");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_city_list()
    {
        $stmt = $this->con->prepare("SELECT `id`,`name` FROM `city` where `status`='enable' order by id desc");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function add_case($case_no, $year, $company_id, $docs, $opp_name, $court_name, $city_id, $sr_date, $case_type, $handle_by, $applicant, $stage, $multiple_images, $added_by, $user_type, $complainant_advocate, $respondent_advocate, $date_of_filing, $next_date)
    {
        $status = "pending";
        // echo "INSERT INTO `case` (`case_no`, `year`, `case_type`, `stage`, `company_id`, `handle_by`, `docs`, `applicant`, `opp_name`, `court_name`, `city_id`, `sr_date`, `status`, `complainant_advocate`, `respondent_advocate`, `date_of_filing`,`next_date`) VALUES ($case_no, $year, $case_type, $stage, $company_id, $handle_by, $docs, $applicant, $opp_name, $court_name, $city_id, $sr_date, $status, $complainant_advocate, $respondent_advocate, $date_of_filing, $next_date)";
        $stmt = $this->con->prepare("INSERT INTO `case` (`case_no`, `year`, `case_type`, `stage`, `company_id`, `handle_by`, `docs`, `applicant`, `opp_name`, `court_name`, `city_id`, `sr_date`, `status`, `complainant_advocate`, `respondent_advocate`, `date_of_filing`,`next_date`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("siiiiisssiissssss", $case_no, $year, $case_type, $stage, $company_id, $handle_by, $docs, $applicant, $opp_name, $court_name, $city_id, $sr_date, $status, $complainant_advocate, $respondent_advocate, $date_of_filing, $next_date);
        $result = $stmt->execute();
        $stmt->close();

        $id = mysqli_insert_id($this->con);

        if ($multiple_images != null) {
            for ($i = 0; $i < sizeof($multiple_images); $i++) {
                $stmt = $this->con->prepare("INSERT INTO `multiple_doc`(`c_id`, `docs`, `added_by`, `user_type`) VALUES (?,?,?,?)");
                $stmt->bind_param("isis", $id, $multiple_images[$i], $added_by, $user_type);
                $result = $stmt->execute();
                $stmt->close();
            }
        }
        return $result;
    }

    public function stage_list($case_id)
    {
        $stmt = $this->con->prepare("SELECT * FROM `stage` WHERE status = 'enable' AND `case_type_id` = (select case_type from `case` where id = ?) ;");
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function next_stage($case_id, $next_stage, $next_date)
    {
        // echo "UPDATE `case` set next_date = $next_date , stage = $next_stage where id = $case_id";
        $stmt = $this->con->prepare("UPDATE `case` set next_date = ? , stage = ? where id = ?");
        $stmt->bind_param("sii", $next_date, $next_stage, $case_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function get_case_history()
    {
        $stmt = $this->con->prepare("SELECT a.id,a.case_no , a.applicant , a.opp_name , a.sr_date , a.court_name ,b.name as court_name,c.case_type, d.name as city_name , e.name as 'handle_by',a.complainant_advocate,a.respondent_advocate,a.date_of_filing,a.next_date,DATEDIFF(CURRENT_DATE , a.sr_date) as case_counter from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id join advocate as e on a.handle_by = e.id order by a.id desc;");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_advocate_list()
    {
        $stmt = $this->con->prepare("SELECT *  from advocate order by id desc"); // updated by jay 25-01
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function delete_task($task_id)
    {
        $stmt = $this->con->prepare("DELETE from `task` where id = ?");
        $stmt->bind_param('i', $task_id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $stmt = $this->con->prepare("DELETE from `case_hist` where task_id = ?");
            $stmt->bind_param('i', $task_id);
            $result2 = $stmt->execute();
            $stmt->close();
        }

        return $result2;
    }
    public function delete_intern($intern_id)
    {

        $stmt = $this->con->prepare("SELECT count(*) as count from `task` where alloted_to  = ?");
        $stmt->bind_param('i', $intern_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result["count"] > 0) {
            return false;
        }

        $stmt = $this->con->prepare("DELETE from `interns` where id = ?");
        $stmt->bind_param('i', $intern_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function delete_advocate($advocate_id)
    {

        $stmt = $this->con->prepare("SELECT count(*) as count from `task` where alloted_by  = ?");
        $stmt->bind_param('i', $advocate_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result['count'] > 0) {
            return false;
        }

        $stmt = $this->con->prepare("DELETE from `advocate` where id = ?");
        $stmt->bind_param('i', $advocate_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;

    }
    public function delete_company($company_id)
    {
        $stmt = $this->con->prepare("SELECT count(*) as count from `case` where company_id  = ?");
        $stmt->bind_param('i', $company_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result['count'] > 0) {
            return false;
        }

        $stmt = $this->con->prepare("DELETE from `company` where id = ?");
        $stmt->bind_param('i', $company_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function get_case_info($case_id)
    {
        $stmt = $this->con->prepare("SELECT c.id,c.case_no,c.year , t.case_type , st.stage as stage_name, cmp.name as company_name, ad.name as advocate_name, c.docs , c.applicant , c.opp_name , crt.name as court_name , ct.name as city_name, c.next_date , st2.stage as next_stage , c.sr_date ,c.complainant_advocate,c.respondent_advocate,c.date_of_filing,c.next_date, DATEDIFF(CURRENT_DATE , c.sr_date) as case_counter from `case` as c join case_type as t on t.id = c.case_type join stage as st on st.id = c.stage left join stage as st2 on st2.id = c.next_stage join company as cmp on cmp.id = c.company_id join advocate as ad on ad.id = c.handle_by join court as crt on crt.id = c.court_name join city as ct on ct.id = c.city_id where c.id = ?;");
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function add_task($case_id, $alloted_to, $instrctions, $alloted_by, $alloted_date, $expected_end_date, $status, $remark)
    {
        $actions = "advocate";
        $stmt = $this->con->prepare("INSERT INTO `task`(`case_id`, `alloted_to`, `instruction`, `alloted_by`, `action_by`, `alloted_date`, `expected_end_date`, `status`, `remark`) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iisisssss', $case_id, $alloted_to, $instrctions, $alloted_by, $actions, $alloted_date, $expected_end_date, $status, $remark);
        $result = $stmt->execute();
        $stmt->close();
        return $result;

    }
    public function get_unassigned_case_list()
    {
        $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.year, a.sr_date, b.name as court_name, a.applicant, a.opp_name, c.case_type, d.name as city_name,a.handle_by,a.complainant_advocate,a.respondent_advocate,a.date_of_filing,a.next_date, DATEDIFF(CURRENT_DATE , a.sr_date) as case_counter from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id where a.id  not in (select DISTINCT(case_id) from task) order by a.id desc;");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function edit_intern($intern_id, $name, $contact, $email, $status,$password) // updated by jay 25-01
    {
        $stmt = $this->con->prepare("UPDATE `interns` set `name`=?,`contact`=?,`email`=?,`status`=?,`password`=? where `id`=?");
        $stmt->bind_param('sssssi', $name, $contact, $email, $status,$password, $intern_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function edit_advocate($advocate_id, $name, $contact, $email, $status, $password) // updated by jay 25-01
    {
        $stmt = $this->con->prepare("UPDATE `advocate` set `name`=?,`contact`=?,`email`=?,`status`=?,`password` = ? where `id`=?");
        $stmt->bind_param('sssssi', $name, $contact, $email, $status,$password, $advocate_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function edit_company($company_id, $name, $contact_person, $contact_no, $status)
    {
        $stmt = $this->con->prepare("UPDATE `company` set `name`=?,`contact_person`=?,`contact_no`=?,`status`=? where `id`=?");
        $stmt->bind_param('ssssi', $name, $contact_person, $contact_no, $status, $company_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function get_case_documents($case_no)
    {
        $stmt = $this->con->prepare("SELECT c1.case_no,c2.case_type,c1.docs,c1.id as file_id,'main' as file_type,c1.sr_date as date_time,'admin' as handled_by,'admin' as user_type from `case` c1,case_type c2 WHERE c1.case_type=c2.id  and  c1.id=? and docs!='' union SELECT c1.case_no,c2.case_type,m.docs,m.id as file_id ,'sub' as file_type,m.date_time,m.added_by as handled_by,m.user_type from `case` c1,case_type c2,multiple_doc m WHERE c1.case_type=c2.id and   m.c_id=c1.id and c1.id=?");
        $stmt->bind_param('ii', $case_no, $case_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function get_case_task($case_no)
    {
        $stmt = $this->con->prepare("SELECT t.id,t.case_id,t.alloted_to as alloted_to_id , t.instruction,t.alloted_by as alloted_by_id ,t.action_by,t.alloted_date,t.expected_end_date,t.status,t.reassign_status,t.remark, c.case_no as 'case_num',i.name as alloted_to,ad.name as alloted_by from task as t join `case` as c on c.id = t.case_id join interns as i on i.id = t.alloted_to join advocate as ad on ad.id = t.alloted_by where t.case_id = ? order by t.id desc");
        $stmt->bind_param("s", $case_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function edit_task($task_id, $case_id, $alloted_to, $instructions, $alloted_by, $alloted_date, $expected_end_date, $status, $remark)
    {

        $stmt = $this->con->prepare("UPDATE `task` SET `case_id`=?,`alloted_to`=?,`instruction`=?,`alloted_by`=?,`alloted_date`=?,`expected_end_date`=?,`status`=?,`remark`=? WHERE `id`=?");
        $stmt->bind_param('iisissssi', $case_id, $alloted_to, $instructions, $alloted_by, $alloted_date, $expected_end_date, $status, $remark, $task_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function stage_court_list($case_id)
    {
        //getting list of stage based on the case type id
        $stmt = $this->con->prepare("SELECT * from stage where case_type_id = ? and status = 'enable' order by id desc");
        $stmt->bind_param('i', $case_id);
        $stmt->execute();
        $result1 = $stmt->get_result();
        $stmt->close();

        //getting list of court based on the case type id
        $stmt = $this->con->prepare("SELECT * from court where case_type = ? and status = 'enable' order by id desc");
        $stmt->bind_param('i', $case_id);
        $stmt->execute();
        $result2 = $stmt->get_result();
        $stmt->close();

        //returning both the query response in an array
        return [$result1, $result2];
    }

    public function get_task_history($task_no)
    {
        $stmt = $this->con->prepare("SELECT * from case_hist where task_id = ? order by id desc");
        $stmt->bind_param("s", $task_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_assigned_case_list()
    {
        $stmt = $this->con->prepare("SELECT a.id, a.case_no, a.year, a.sr_date, b.name as court_name, a.applicant, a.opp_name, c.case_type, d.name as city_name,ad.name as advocate_name,a.complainant_advocate,a.respondent_advocate,a.date_of_filing,a.next_date, DATEDIFF(CURRENT_DATE , a.sr_date) as case_counter from `case` as a join `court` as b on a.court_name = b.id join `case_type` as c on a.case_type = c.id join city as d on a.city_id = d.id join advocate as ad on ad.id = a.handle_by where a.id in (select DISTINCT(case_id) from task) order by a.id desc;");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function get_interns_list()
    {
        $stmt = $this->con->prepare("SELECT * FROM `interns` order by id desc"); // updated by jay 25-01
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
    public function task_assignment($case_id, $alloted_to, $alloted_by, $remark, $expected_end_date, $instruction)
    {
        $status = "allocated";
        $stmt = $this->con->prepare("INSERT into `task` (`case_id`,`alloted_to`,`alloted_by`,`remark`,`status`,`expected_end_date`,`instruction`) values (?,?,?,?,?,?,?)");
        $stmt->bind_param('iiissss', $case_id, $alloted_to, $alloted_by, $remark, $status, $expected_end_date, $instruction);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function add_company($name, $contact_person, $contact_no)
    {
        $status = 'enable';
        $stmt = $this->con->prepare("INSERT into `company` (`name`,`contact_person`,`contact_no`,`status`) values (?,?,?,?)");
        $stmt->bind_param('ssss', $name, $contact_person, $contact_no, $status);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    public function get_company_list()
    {
        $stmt = $this->con->prepare("SELECT * from `company` order by id desc");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
}

?>