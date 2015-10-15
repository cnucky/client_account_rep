<?php

if (!defined("WHMCS"))
  die("This file cannot be accessed directly");

function client_account_rep_config()
{
  $configarray = array(
    "name" => "Client Account Rep",
    "description" => "Map your clients to an account rep and assign an optional commission percentage",
    "version" => "1.0",
    "author" => "Down South Hosting <hello@downsouth.hosting>",
    "language" => "english",
    "fields" => array(),
  );

  return $configarray;
}

function client_account_rep_activate()
{
  $query[0] = "
    CREATE TABLE IF NOT EXISTS `mod_client_account_rep`
    (
    	`id` int(11) NOT NULL auto_increment PRIMARY KEY,
	  	`tblClientsId` int(11) UNIQUE NOT NULL DEFAULT 0,
      `tblAdminsId` int(11) NOT NULL DEFAULT 0,
      `commissionPercent` DECIMAL(5,2) NOT NULL DEFAULT 0.00
    )
  ";

  $query[1] = "
    CREATE TABLE IF NOT EXISTS `mod_client_account_commissions`
    (
      `id` int(11) NOT NULL auto_increment PRIMARY KEY,
      `tblInvoicesId` int(11) UNIQUE NOT NULL DEFAULT 0,
      `tblClientsId` int(11) NOT NULL DEFAULT 0,
      `tblAdminsId` int(11) NOT NULL DEFAULT 0,
      `commissionPercent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
      `commissionAmount` DECIMAL(7,2) NOT NULL DEFAULT 0.00,
      `year` int(4) NOT NULL DEFAULT 0,
      `month` int(2) NOT NULL DEFAULT 0,
      `day` int(2) NOT NULL DEFAULT 0
    )
  ";

  foreach($query as $q)
  {
    mysql_query($q);
  }
}

function client_account_rep_deactivate()
{}

function client_account_rep_upgrade($vars)
{}

function client_account_rep_output($vars) {

  $html = '';
  $modulelink = $vars['modulelink'];

  if($_POST)
  {
    $postArray = array();

    if(isset($_POST['clientId']))
    {
      $clientId = (int) $_POST['clientId'];
      $postArray['clientId'] = $clientId;
    }

    if(isset($_POST['adminId']))
    {
      $adminId = (int) $_POST['adminId'];
      $postArray['adminId'] = $adminId;
    }

    if(isset($_POST['commissionPercent']))
    {
      $commission = $_POST['commissionPercent'];
      $postArray['commission'] = $commission;
    }

    function validateFullInput($validateArray)
    {

      $digitsPattern = "/^\d+$/";
      $commissionPattern = "/^[0-9]{1,3}\.[0-9]{1,2}$/";

      if(!preg_match($digitsPattern, $validateArray['clientId'])
        || !preg_match($digitsPattern, $validateArray['adminId'])
        || !preg_match($commissionPattern, $validateArray['commission']))
      {
        $html .= '<div class="row"><div class="col-xs-12"><span class="alert-danger">Invalid Input.  Try adding again.</span></div></div>';
      }else{
        echo 'true';
        return True;
      }
    }

    function validateDeleteInput($clientId)
    {

      $digitsPattern = "/^\d+$/";
      $commissionPattern = "/^[0-9]{1,3}\.[0-9]{1,2}$/";

      if(!preg_match($digitsPattern, $clientId))
      {
        $html .= '<div class="row"><div class="col-xs-12"><span class="alert-danger">Invalid Input.  Try adding again.</span></div></div>';
      }else{
        return True;
      }
    }

    if ($_POST['_add'])
    {
      if(validateFullInput($postArray))
      {
        $clientInsertMappingQuery = insert_query('mod_client_account_rep', array('tblClientsId' => $clientId, 'tblAdminsId' => $adminId, 'commissionPercent' => $commission));

        if($clientInsertMappingQuery)
        {
          $html .= '<div class="row"><div class="col-xs-12"><span class="alert-success">Client / Employee Mapping added!</span></div></div>';
        }else{
          $html .= '<div class="row"><div class="col-xs-12"><span class="alert-danger">Record not inserted. Try again.</span></div></div>';
        }
      }
    }

    if ($_POST['_delete'])
    {
      if(validateDeleteInput($_POST['clientId']))
     {
        $clientDeleteMappingQuery = full_query("DELETE FROM mod_client_account_rep WHERE tblClientsId=$clientId");

        if($clientDeleteMappingQuery)
        {
          $html .= '<div class="row"><div class="col-xs-12"><span class="alert-success">Client / Employee Mapping deleted!</span></div></div>';
        }else{
          $html .= '<div class="row"><div class="col-xs-12"><span class="alert-danger">Record not deleted. Try again.</span></div></div>';
        }
      }
    }

    if ($_POST['_update'])
    {

      if(validateFullInput($postArray))
      {
        $clientUpdateMappingQuery = update_query('mod_client_account_rep', array('tblAdminsId' => $adminId, 'commissionPercent' => $commission), array('tblClientsId' => $clientId));

        if($clientUpdateMappingQuery)
        {
          $html .= '<div class="row"><div class="col-xs-12"><span class="alert-success">Client / Employee Mapping updated!</span></div></div>';
        }else{
          $html .= '<div class="row"><div class="col-xs-12"><span class="alert-danger">Record not updated. Try again.</span></div></div>';
        }
      }
    }
  }

	$clientRepsQuery = select_query('mod_client_account_rep', "tblClientsId, tblAdminsId, commissionPercent", array());
  while($row = mysql_fetch_array($clientRepsQuery, MYSQL_ASSOC))
  {
    $clientRepsArray[] = $row;
  }

  $allActiveClientsQuery = select_query('tblclients', "id,firstname,lastname,companyname", array('status' => 'Active'));
  while($row = mysql_fetch_array($allActiveClientsQuery, MYSQL_ASSOC))
  {
    $allActiveClientsArray[] = $row;
  }

  $clientIdArray = array();
  foreach($allActiveClientsArray as $client)
  {
    $clientIdArray[] = $client['id'];
  }

  $allAdminsQuery = select_query('tbladmins', "id, firstname, lastname, username", array('disabled' => 0));
  while($row = mysql_fetch_array($allAdminsQuery, MYSQL_ASSOC))
  {
    $allAdminsArray[] = $row;
  }

  $clientRepsClientIdsQuery = select_query('mod_client_account_rep', "tblClientsId", array());
  while($row = mysql_fetch_array($clientRepsClientIdsQuery, MYSQL_ASSOC))
  {
    $clientRepsClientIdsArray[] = $row['tblClientsId'];
  }

  if(!$clientRepsClientIdsArray)
  {
    $clientRepsClientIdsArray = array();
  }
  $clientsWithoutDataArray = array_diff($clientIdArray, $clientRepsClientIdsArray);

  $html .= '
    <form method="POST" action="' . $modulelink . '" name="addForm">
      <input type="hidden" name="_add" value="1">
      <div class="row">
        <div class="col-xs-4" style="font-weight: bold">CLIENT</div>
        <div class="col-xs-4" style="font-weight: bold">EMPLOYEE</div>
        <div class="col-xs-2" style="font-weight: bold">COMMISSION</div>
        <div class="col-xs-2" style="font-weight: bold">Created by<br><a href="https://downsouth.hosting" target="_blank"><img src="../modules/addons/client_account_rep/img/dsh_logo.png" class="img-responsive"></a></div>
      </div>
      <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-4">
  ';

  if($clientsWithoutDataArray)
  {
    $html .= '<select name="clientId">';

    foreach( $clientsWithoutDataArray as $cWithoutData )
    {
      foreach( $allActiveClientsArray as $client )
      {
        if( $client['id'] == $cWithoutData )
        {
          $html .= '<option value="' . $client['id'] . '">' . $client['id'] . ' - ';

          if( $client['companyname'] == '' )
          {
            $html .= $client['lastname'] . ', ' . $client['firstname'];
          }else{
            $html .= $client['companyname'];
          }

        }
        $html .= '</option>';
      }
    }

    $html .= '</select>';
  }else{
    $html .= 'All clients listed below';
  }

  $adminsDropdown = '<select name="adminId">';
  foreach( $allAdminsArray as $admin )
  {
    $adminsDropdown .= '<option value="' . $admin['id'] . '">' . $admin['id'] . ' - ' . $admin['firstname'] . ' ' . $admin['lastname'] .'</option>';
  }
  $adminsDropdown .= '</select>';

  $html .= '
        </div>

        <div class="col-xs-12 col-sm-12 col-md-4">' . $adminsDropdown . '</div>
        <div class="col-xs-6 col-sm-6 col-md-2"><input type="text" size="2" name="commissionPercent" value="" placeholder="5.00">%</div>
        <div class="col-xs-6 col-sm-6 col-md-2"><button value="Add" class="btn btn-success btn-xs" type="submit" id="submit">Add</div>

      </div>

    </form>

    <div class="row"><div class="col-xs-12"><hr></div></div>
  ';

  if($clientRepsArray)
  {
    asort($clientRepsArray);
    foreach($clientRepsArray as $clientRep)
    {
      $activeAdminId = $clientRep['tblAdminsId'];

      $html .= '
        <form method="POST" action="' . $modulelink . '" name="updateForm' . $clientRep['tblClientsId'] . '">
        <input type="hidden" name="_update" value="1">
        <input type="hidden" name="clientId" value="' . $clientRep['tblClientsId'] . '">
        <div class="row">
          <div class="col-xs-4 col-sm-4 col-md-4">
      ';

      foreach($allActiveClientsArray as $client)
      {
        if( $client['id'] == $clientRep['tblClientsId'] )
        {
          if( $client['companyname'] == '' )
          {
            $html .= '<a href="clientssummary.php?userid=' . $client['id'] . '" target="_blank">' . $clientRep['tblClientsId'] . ' - ' . $client['lastname'] . ', ' . $client['firstname'] . '</a>';
          }else{
            $html .= '<a href="clientssummary.php?userid=' . $client['id'] . '" target="_blank">' . $clientRep['tblClientsId'] . ' - ' . $client['companyname'] . '</a>';
          }
        }
      }

      $html .= '
          </div>
          <div class="col-xs-4 col-sm-4 col-md-4">
            <select name="adminId">
      ';

      foreach($allAdminsArray as $admin)
      {
        $html .= '<option value="' . $admin['id'] . '" ';
        if( $admin['id'] == $clientRep['tblAdminsId'] )
        {
          $html .= 'selected';
        }
        $html .= '>' . $admin['id'] . ' - ' . $admin['firstname'] . ' ' . $admin['lastname'] . '</option>';
      }
      $html .= '
            </select>
          </div>
          <div class="col-xs-1 col-sm-1 col-md-1"><input type="text" size="2" name="commissionPercent" value="' . $clientRep['commissionPercent'] . '">%</div>
          <div class="col-xs-1 col-sm-1 col-md-1">
              <button value="Update" class="btn btn-warning btn-xs" type="submit" id="submit">Update</button>
            </form>
          </div>
          <div class="col-xs-1 col-sm-1 col-md-1">
            <form method="POST" action="' . $modulelink . '" name="deleteForm' . $clientRep['tblClientsId'] . '">
              <input type="hidden" name="_delete" value="1">
              <input type="hidden" name="clientId" value="' . $clientRep['tblClientsId'] . '">
              <button value="Delete" class="btn btn-danger btn-xs" type="submit" id="submit">Delete</button>
            </form>
          </div>
        </div>
      ';
    }
  }

  $html .='
    </form>
  ';

  print $html;
}

function client_account_rep_sidebar($vars)
{}

?>
