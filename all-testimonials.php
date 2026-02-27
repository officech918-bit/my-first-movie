/**
 * All Testimonials Page
 *
 * This page allows administrators and webmasters to view all testimonials.
 * It includes user authentication and role-based content display.
 *
 * @package     MyFirstMovie
 * @subpackage  Pages
 * @since       1.0.0
 */
declare(strict_types=1);

// Bootstrap the application
require_once 'inc/requires.php';

// $database and $user are initialized in requires.php
$menu = 'inc/left-menu-user.php';

// User authentication and role handling
if ($user->check_session()) {
    $user_type = $_SESSION['user_type'] ?? 'user';
    switch ($user_type) {
        case 'webmaster':
            $menu = 'inc/left-menu-webmaster.php';
            break;
        case 'admin':
            $menu = 'inc/left-menu-admin.php';
            break;
        default:
            // The default user is already a 'user'
            break;
    }
} else {
    header("Location: index.php");
    exit();
}

$sitename = $user->get_sitename();
$sub_location = $user->get_sub_location();

$path = rtrim($sitename . '/' . $sub_location, '/') . '/';
$direct_path = rtrim($_SERVER['DOCUMENT_ROOT'] . '/' . $sub_location, '/') . '/';

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
<title>All Testimonials</title>
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
			Managed All Testimonials 
			</h3>
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li>
						<i class="fa fa-home"></i>
						<a href="dashboard.php">Dashboard</a>
						<i class="fa fa-angle-right"></i>
					</li>
                     
					<li>
						<a href="#">All Testimonials</a>
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
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-globe"></i>All Testimonials
							</div>
							<div class="tools">
								<a href="javascript:;" class="collapse">
								</a>
							</div>
						</div>
						<div class="portlet-body">
                        	<?php if (isset($_SESSION['msg1'])) : ?>
                                <div class="alert alert-success">
                                    <strong>Success!</strong> <?php echo htmlspecialchars($_SESSION['msg1'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <?php unset($_SESSION['msg1']); ?>
                            <?php endif; ?>
							<div class="table-toolbar">
								<div class="row">
									<div class="col-md-6">
										<div class="btn-group">
											<a id="sample_editable_1_new" href="testimonials.php" class="btn green">
											Add New <i class="fa fa-plus"></i>
											</a>
										</div>
									</div>
									<div class="col-md-6">
										<div class="btn-group pull-right">

										</div>
									</div>
								</div>
							</div>
                            <div id="loading" style="position:fixed; top:50%; left:50%; width:100px; height:100px;"></div>
                                <div id="container">
                <div class="data"></div>
                <div class="pagination"></div>
              </div>
              

							
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
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
<script>
jQuery(document).ready(function() {       
   Metronic.init(); // init metronic core components
Layout.init(); // init current layout
QuickSidebar.init(); // init quick sidebar
Demo.init(); // init demo features
   TableManaged.init();
   ComponentsPickers.init();
});
</script>

<!-- pagination starts -->
<script type="text/javascript">
	function loading_show(){
		
			   $('#loading').html("<img src='assets/admin/layout/img/loading.gif' style='absolute: relative; top: 50%; left: 50%;'/>").fadeIn('fast');
			}
			function loading_hide(){
				$('#loading').fadeOut('fast');
			}                
			function loadData(page, data_per_page, search_term, from, to){
				loading_show(); 
				//alert('ok...');

				$.ajax({
					'url' : 'load_all_testimonials.php',
					'type' : 'POST', //the way you want to send data to your URL
					'data' : {'page' : page, 'data_per_page' : data_per_page, 'search_term' : search_term, 'from' : from, 'to' : to},
					'success' : function(data){ //probably this request will return anything, it'll be put in var "data"
						//var container = $('#container'); //jquery selector (get element by id)
						if(data){
							loading_hide();
							//$("#container").empty();
							$("#container").html(data);
						}
					}
				});
			}
			
		$(document).ready(function(){
			loadData(1, 10, '');  // For first time page load default results
			$(document).on('click', '#container .pagination li.active_btn', function(e) { 
				var page = $(this).attr('p');
				var data_per_page = $( "#per_page_control" ).val();
				var search_term = $( "#search_data" ).val();
				var from = $( "#from" ).val();
				var to = $( "#to" ).val();
				loadData(page, data_per_page, search_term, from, to);
			});
			
			/*$('#container .pagination li.active').live('click',function(){
				alert('ok')
				var page = $(this).attr('p');
				loadData(page);
				
			});
			*/
			$(document).on('change', '#per_page_control', function(e) {
					var data_per_page = $(this).val();
					var search_term = $( "#search_data" ).val();
					var from = $( "#from" ).val();
					var to = $( "#to" ).val();
					loadData(1, data_per_page, search_term, from, to);
			
			});	 
			$(document).on('click', '.go_button', function(e) {           
			//$('#go_btn').live('click',function(){
				var page = parseInt($('.goto').val());
				var no_of_pages = parseInt($('.total').attr('a'));
				var data_per_page = $( "#per_page_control" ).val();
				var search_term = $( "#search_data" ).val();
				var from = $( "#from" ).val();
				var to = $( "#to" ).val();
				
				if(page != 0 && page <= no_of_pages){
					loadData(page, data_per_page, search_term, from, to);
				}else{
					alert('Enter a PAGE between 1 and '+no_of_pages);
					$('.goto').val("").focus();
					return false;
				}
				
			});
		});
			
			/* $(document).on('change keyup', '#search_data', function(e) {
				var Temp = $(this).val();
				alert(Temp)
			});
			*/	
			$('#search_btn').live('click', function() {
				var search_term = $('#search_data').val();
				//alert(Temp);
				var data_per_page = $( "#per_page_control" ).val();
				var from = $( "#from" ).val();
				var to = $( "#to" ).val();
				
				loadData(1, data_per_page, search_term, from, to);
			});
			
	</script>
    
    <script type="text/javascript" src="assets/admin/layout/scripts/highlight.js"></script>
<script type="text/javascript" src="assets/admin/layout/scripts/zebra_dialog.js"></script>
<script type="text/javascript">
    hljs.initHighlightingOnLoad();
</script>
<script type="text/javascript">
    $(document).ready(function() {
		$('.date-picker').live('click', function(e) {
			$('.date-picker').datepicker({
					rtl: Metronic.isRTL(),
					orientation: "left",
					format: 'dd/mm/yyyy',
					autoclose: true
				});
		});
		
        $('.example36').live('click', function(e) {
			var msg = $("#access_msg").val();
			if(msg == "allowed")
			{
				var temp = this.title;
				var arr = temp.split('|');
				//var index = temp.indexOf("|");
				//var name = temp.substring(0, index);
				//var user_id = temp.substring(index+2);
				var name = arr[0];
				var user_id = arr[1];
				//alert(user_id);

				e.preventDefault();
				$.Zebra_Dialog('<strong>Are you sure</strong>, you want to delete ' + name, {
					'type':     'question',
					'title':    'Confirmation',
					'buttons':  ['Yes', 'No'],
					'onClose':  function(caption) {
						if(caption == 'Yes'){
							$.ajax({ url: 'delete_testimonial.php',
							data: {id: user_id},
							type: 'post',
							success: function(output) {
								//alert(client_name + ' Deleted Successfully');
								$.Zebra_Dialog(name + ' Deleted Successfully', {
								'type':     'confirmation',
								'title':    'Confirmation',
								 'onClose':  function() {
								   location.reload();
								}
							});
								
							}
						});

                    }
                }
            });
			
		}
		else
		{
			$.Zebra_Dialog(' Sorry! You are not allowed to delete testimonials', {
								'type':     'confirmation',
								'title':    'Confirmation',
								 
							});
		}
        });
    });
</script> 
<?php unset($_SESSION['msg1']); ?>
</body>
<!-- END BODY -->
</html>