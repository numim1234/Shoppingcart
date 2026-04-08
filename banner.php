<section class="banner-section" style="background: #f7f3ef;">
	<div class="banner-circle"></div>

	<div class="container">
		<div class="home-banner">
			<div class="row align-items-center">

				<!-- LEFT CONTENT -->
				<div class="col-lg-7">
					<div class="section-search aos" data-aos="fade-up">
						<h1>ค้นหาสินค้าที่คุณต้องการ<br>
							<span style="color:#d6336c;">ได้ที่นี่ !!</span>
						</h1>

						<p>สินค้า ผลิตภัณฑ์ ประเภทสินค้า ค้นหาได้อย่างรวดเร็ว</p>

						<div class="search-box">
							<form action="index.php" method="get" class="d-flex flex-wrap">

								<div class="search-input line flex-grow-1">
									<div class="form-group mb-0">
										<div class="group-img">
											<input type="text" name="keyword" class="form-control"
												placeholder="ค้นหาสินค้า"
												value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
											<i class="fa fa-search"></i>
										</div>
									</div>
								</div>

								<div class="search-btn">
									<button class="btn btn-danger" type="submit">
										<i class="fa fa-search"></i> ค้นหา
									</button>
								</div>

							</form>
						</div>
					</div>
				</div>

				<!-- RIGHT IMAGE (เปลี่ยนเป็น SUKSUD) -->
				<div class="col-lg-5 text-center">
					<div class="banner-imgs">
						<img src="banner/su.png" class="img-fluid banner-img">
					</div>
				</div>

			</div>
		</div>
	</div>
</section>