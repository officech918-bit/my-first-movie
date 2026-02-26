<?php
	date_default_timezone_set('Asia/Kolkata');
	$date = date("Y-m-d H:i:s", time());
	
	//get class files
	include('inc/requires.php');
	include('classes/class.admin.php');
	include('classes/class.webmaster.php');

	// Load environment variables from .env file
	if (class_exists('Dotenv\Dotenv')) {
		$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
		$dotenv->load();
	}
	
	//create objects
	$database = new MySQLDB();
	$user = new visitor();
	$is_edit = false;
	$menu = 'inc/left-menu-user.php';
	
	//check if the user is not logged in
	if(!$user->check_session())
	{	
		header("location: index.php"); 
		exit();
	} else if ($_SESSION['user_type'] == 'webmaster'){
		$user = new webmaster();
		$menu = 'inc/left-menu-webmaster.php';		
		$wm_first_name = $user->get_wm_first_name();
		$wm_last_name = $user->get_wm_last_name();
		
	} else if ($_SESSION['user_type'] == 'admin'){
 		$user = new admin();
 		$menu = 'inc/left-menu-admin.php';
 	} else {
 		$user = new user();
 	}

	// Generate and store a CSRF token if one doesn't exist
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	$csrf_token = $_SESSION['csrf_token'];

 	
 	$sitename = $user->get_sitename();
 	$sub_location = $user->get_sub_location();
 	$admin_location = $user->get_admin_location();
	
	$path = "";
	$direct_path = "";
	if($sub_location != ""){
		$path = $sitename.'/'.$sub_location.'/';
		$direct_path = $_SERVER['DOCUMENT_ROOT'].'/'.$sub_location.'/';		
	}
	else {
		$path = $sitename.'/';
		$direct_path = $_SERVER['DOCUMENT_ROOT'].'/';
	}

	// Get the correct base path from current request
	$script = $_SERVER['SCRIPT_NAME'] ?? '';
	$basePath = '';
	if ($script) {
		$parts = explode('/', trim($script, '/'));
		if (!empty($parts)) {
			$basePath = '/' . $parts[0]; // This will give us /myfirstmovie3
		}
	}
	$correct_base_path = $basePath;

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>Sort Data</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta content="" name="description"/>
<meta content="" name="author"/>
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
<link href="assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="assets/global/plugins/select2/select2.css"/>
<link rel="stylesheet" type="text/css" href="assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="assets/global/plugins/bootstrap-datepicker/css/datepicker3.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN THEME STYLES -->
<link href="assets/global/css/components.css" id="style_components" rel="stylesheet" type="text/css"/>
<link href="assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
<link id="style_color" href="assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
<link href="assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
<!-- END THEME STYLES -->

 <!-- zebra modal box -->
<link rel="stylesheet" href="assets/admin/layout/css/zebra_dialog.css" type="text/css">
<link rel="shortcut icon" href="favicon.ico"/>

<style>
.outlined {
color: #3675B4;
border: solid 2px #3675B4;
border-radius: 3px;
text-transform: uppercase;
background: #fff;
font-size: 18px;
}

.mleft_no {
margin-left: 0;
}


.gallery{ width:100%; float:left; margin-top:10px;}

.gallery ul{ margin:0; padding:0; list-style-type:none;}

.gallery ul li{ padding:7px; border:2px solid #ccc; float:left; margin:10px 7px; background:none; width:auto; height:auto;}

#reorder-helper{margin: 18px 10px;
padding: 10px;}
.light_box {
background: #efefef;
padding: 20px;
margin: 10px 0;
text-align: center;
font-size: 1.2em;
}

/* NOTICE */
.notice, .notice a{ color: #fff !important; }
.notice { z-index: 8888; }
.notice a { font-weight: bold; }
.notice_error { background: #E46360; }
.notice_success { background: #657E3F; }

.gallery img{ width:250px;}
</style>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="page-header-fixed page-quick-sidebar-over-content ">
<!-- BEGIN HEADER -->
<?php include('inc/header.php'); ?>
<!-- END HEADER -->
<div class="clearfix">
</div>
<!-- BEGIN CONTAINER -->
<div class="page-container">
	<!-- BEGIN SIDEBAR -->
	<div class="page-sidebar-wrapper">
		<!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
		<!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
		<div class="page-sidebar navbar-collapse collapse">
			<!-- BEGIN SIDEBAR MENU -->
			<?php include($menu); ?>
			<!-- END SIDEBAR MENU -->
		</div>
	</div>
	<!-- END SIDEBAR -->
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			<!-- BEGIN PAGE HEADER-->
			<h3 class="page-title">
			Sort Data 
			</h3>
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="dashboard.php">Dashboard</a>
						<i class="fa fa-angle-right"></i>
					</li>
					<li>
						<a href="#">Sort Data</a>
					</li>
				</ul>
				<div class="page-toolbar">
					<div class="btn-group pull-right">
						
					</div>
				</div>
			</div>
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
			<div class="row">
				<div class="col-md-12">
                	<div class="portlet box blue">
                        <div class="portlet-title">
                          <div class="caption"> <i class="fa fa-gift"></i> Sort All Data </div>
                        </div>
                        <div class="portlet-body">
                            <div class="row">
                              <div class="col-md-6">
                                <div class="form-group">
                                    <div class="input-group">
                                            <div class="input-icon">
                                                <i class="fa fa-search"></i>
                                                <select  class="form-control validate[required]" name="sorting_tbl" id="sorting_tbl">
                                                      <option value="">Select a Section</option>
                                                      <!--  value would be as table_name|type (text/image)|type_field|text_field|image_path|where_field|where_value -->
                                                      <option value="testimonials|image|logo|company">Testimonials</option>
                                                      <option value="categories|image|cat_img|title">Categories</option>
                                                      <option value="panelists|image|image|name">Panelists</option>
                                                      <option value="core_team|image|image|name">Core Team</option>
                                                      <option value="seasons|text|title|title">Seasons</option>
                                                      <option value="behind_the_scenes|text|title|title">Behind the scenes</option>
                                                </select>
                                            </div>
                                            <span class="input-group-btn">
                                            <a id="load_sorting_data" class="btn btn-success" type="button"><i class="fa fa-refresh"></i> Load</a>
                                            </span>
                                        </div>
                                </div>
                              </div>
                                <!--/span--> 
                            </div>
                              <form action="" id="fileupload" class="horizontal-form" enctype="multipart/form-data">
                              <input type="hidden" id="correct_base_path" value="<?php echo htmlspecialchars($correct_base_path); ?>">
                              <input type="hidden" id="admin_base_url" value="<?php echo htmlspecialchars($path); ?>">
                              
                              <!--<form class="form-horizontal"> -->
                              <div class="form-body">
                          
                                <div class="row">
                                    
            <div id="reorder-helper" class="light_box" style="display:none;">1. Drag photos to reorder.<br>2. Click 'Save Reordering' when finished.</div>
                                  <div class="gallery">
                                    <ul class="reorder_ul reorder-photos-list section_data">
                                    
                                    </ul>
                                </div>
                                                        
                                  
                                </div>
                              </div>
                              <div class="form-actions">
                                <hr />
                                <a href="javascript:;" class="btn blue mleft_no reorder_link" id="save_reorder">Update Reorder</a>
                              </div>
                              </form>
                        </div>
                      </div>
                
                

                
                

				</div>
			</div>
			
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->

</div>
<!-- END CONTAINER -->
<!-- BEGIN FOOTER -->
<?php include('inc/footer.php'); ?>
<!-- END FOOTER -->
<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
<!-- BEGIN CORE PLUGINS -->
<!--[if lt IE 9]>
<script src="assets/global/plugins/respond.min.js"></script>
<script src="assets/global/plugins/excanvas.min.js"></script> 
<![endif]-->
<script src="assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="assets/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
<script src="assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>

<!-- END CORE PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="assets/global/plugins/select2/select2.min.js"></script>
<script type="text/javascript" src="assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="assets/global/scripts/metronic.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/layout.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/quick-sidebar.js" type="text/javascript"></script>
<script src="assets/admin/layout/scripts/demo.js" type="text/javascript"></script>
<script src="assets/admin/pages/scripts/table-managed.js"></script>
<script src="assets/admin/pages/scripts/components-pickers.js"></script>
<script type="text/javascript" src="assets/admin/layout/scripts/zebra_dialog.js"></script>
<script>
jQuery(document).ready(function() {       
   Metronic.init(); // init metronic core components
	Layout.init(); // init current layout
	//QuickSidebar.init(); // init quick sidebar
	Demo.init(); // init demo features
   //TableManaged.init();
   ComponentsPickers.init();

	$('.reorder_link').on('click', function() {
		var h = [];
		// get order of items
		$("ul.reorder-photos-list li").each(function() {
			h.push($(this).attr('id').substr(9));
		});
		var sort_table = $('#sorting_tbl').val().split('|');
		var table = sort_table[0];
		// save order
		$.ajax({
			type: "POST",
			url: "update_sorting_data.php",
			data: {
				ids: h,
				table: table,
				csrf_token: '<?php echo $csrf_token; ?>'
			},
			beforeSend: function() {
				// loading...
			},
			success: function(data) {
				// success
				new $.Zebra_Dialog('Sorting has been updated successfully.', {
					'type': 'confirmation',
					'title': 'Success'
				});
			},
			error: function() {
				// error
				new $.Zebra_Dialog('An error occurred while updating the sorting.', {
					'type': 'error',
					'title': 'Error'
				});
			}
		});
		return false;
	});
	
	$("#load_sorting_data").click(function(){
		var sort_table = $('#sorting_tbl').val().split('|');
		var table = sort_table[0];
		var type = sort_table[1];
		var type_field = sort_table[2];
		var record_text_field = sort_table[3];
		var correct_base_path = $('#correct_base_path').val();
		var admin_base_url = $('#admin_base_url').val();
		
		if(table != ''){
			$.ajax({
				type: "POST",
				url: "get_sorting_data.php",
				data: {
					table: table,
					type: type,
					type_field: type_field,
					record_text_field: record_text_field,
					correct_base_path: correct_base_path,
					admin_base_url: admin_base_url,
					csrf_token: '<?php echo $csrf_token; ?>'
				},
				beforeSend: function(){
					
				},
				success: function(data){
					$('.section_data').html(data);
					$('#reorder-helper').show();
					$("ul.reorder-photos-list").sortable({ tolerance: 'pointer' });
					$('.reorder_link').css('display','block');
				}
			});
		} else {
			new $.Zebra_Dialog('Please select a section to sort.',{
				'type':     'information',
				'title':    'Information'
			});
		}
	});
	
	$(".reorder_link").css('display','none');

});
</script>
</body>
<!-- END BODY -->
</html>