<?php require_once("head.php");
 session_start(); ?>
<body>
	<div class="main-wrapper innerpagebg">

		<?php require_once("header.php") ?>

		<!-- /Breadscrumb Section -->
		
		<!-- Login Section -->		
		<div class="login-content" style="margin-top: 20px;">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-lg-5 mx-auto pt-3">
						<div class="login-wrap">
							
							<div class="login-header">
								<h3>สมัครสมาชิกผู้ใช้งาน</h3>
								<p>กรุณากรอกรายละเอียดของคุณ</p>								
							</div>
							
							<!-- Login Form -->
							<form method="POST" action="register_db.php" id="registerForm" enctype="multipart/form-data">
                                <div class="form-group">
                                    <div class="pass-group group-img">
                                        <i class="feather-user"></i>
                                        <input type="text" name="m_username" required class="form-control pass-input" placeholder="ไอดีผู็ใช้งาน">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="pass-group group-img">
                                        <i class="feather-lock"></i>
                                        <input type="password" name="m_password" required class="form-control pass-input" placeholder="รหัสผ่านใช้งาน">
                                    </div>
                                </div>
                                <div class="form-group group-img">
                                    <i class="feather-user"></i>
                                    <input type="text" name="m_name" required class="form-control" placeholder="ชื่อผู้ใช้งาน">
                                </div>
                                <div class="form-group group-img">
                                    <i class="feather-mail"></i>
                                    <input type="email" name="m_email" required class="form-control" placeholder="อีเมล">
                                </div>
                                <div class="form-group group-img">
                                    <i class="feather-phone"></i>
                                    <input type="tel" name="m_tel" required class="form-control" placeholder="เบอร์โทรศัพท์">
                                </div>
                                <div class="form-group group-img">
                                    <i class="feather-map-pin"></i>
                                    <input type="text" name="m_address" required class="form-control" placeholder="ที่อยู่">
                                </div>
                                <div class="form-group group-img">
                                    <i class="feather-image"></i>
                                    <input type="file" name="m_img" accept="image/*" class="form-control-file">
                                </div>
                                <input type="hidden" name="m_level" value="member">
                                <input type="hidden" name="m_status" value=0>
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
							