<?php require_once("head.php");
 session_start(); ?>
<body>
	<div class="main-wrapper innerpagebg">

		<?php require_once("header.php") ?>

		<!-- /Breadscrumb Section -->
		
		<!-- Login Section -->		
		<div class="login-content" style="margin-top: 80px;">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-lg-5 mx-auto pt-3">
						<div class="login-wrap">
							
							<div class="login-header">
								<h3>เข้าสู่ระบบใช้งาน</h3>
								<p>กรุณากรอกรายละเอียดของคุณ</p>								
							</div>
							
							<!-- Login Form -->
							<form method="POST" action="check_login.php" id="loginForm">
								<div class="form-group group-img">
								    <div class="group-img">
										<!-- <i class="feather-mail"></i> -->
										<i class="feather-user"></i>
										<input type="text" name="m_username" required class="form-control" placeholder="ชื่อผู้ใช้งาน">
									</div>
								</div>
								<div class="form-group">
									<div class="pass-group group-img">
									    <i class="feather-lock"></i>
										<input type="password" name="m_password" required class="form-control pass-input" placeholder="รหัสผ่านใช้งาน">
										<!-- <span class="toggle-password feather-eye"></span> -->
									</div>
								</div>
								<button class="btn btn-primary w-100 login-btn" type="submit">ตกลง</button>
														
							</form>
							<!-- /Login Form -->
											
						</div>
					</div>
				</div>
				
			</div>
		</div>
		<!-- /Login Section -->	
	</div>
	<?php require_once("footer.php") ?>
</body>
</html>
							