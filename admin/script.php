<!-- jQuery -->
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../plugins/chart.js/Chart.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.js"></script>
<!-- SweetAlert2 -->
<script src="../plugins/sweetalert2/sweetalert2.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>


<script>
    function confirmLogout(event) {
        event.preventDefault(); // หยุดการเปลี่ยนเส้นทางไปยังหน้า logout.php ก่อน
        Swal.fire({
            text: "คุณแน่ใจที่จะออกจากระบบหรือไม่?",
            icon: "info",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "ตกลง",
            cancelButtonText: "ยกเลิก"
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to logout.php if user confirms
                window.location.href = "../logout.php";
            }
        });
    }
</script>
<script>
    $(function() {
        // Initialize example1 only if element exists and not already initialized
        if (typeof $.fn.DataTable !== 'undefined' && $('#example1').length) {
            if (!$.fn.DataTable.isDataTable('#example1')) {
                var dt1 = $("#example1").DataTable({
                    "responsive": true,
                    "lengthChange": true,
                    "autoWidth": false,
                    "order": [
                        [0, 'desc']
                    ],
                    "language": {
                        "sProcessing": "กำลังดำเนินการ...",
                        "sLengthMenu": "แสดง _MENU_ รายการ",
                        "sZeroRecords": "ไม่พบข้อมูล",
                        "sEmptyTable": "ไม่มีข้อมูลในตาราง",
                        "sInfo": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                        "sInfoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                        "sInfoFiltered": "(กรองข้อมูล _MAX_ ทุกรายการ)",
                        "sInfoPostFix": "",
                        "sSearch": "ค้นหา:",
                        "sUrl": "",
                        "oPaginate": {
                            "sFirst": "เริ่มต้น",
                            "sPrevious": "ก่อนหน้า",
                            "sNext": "ถัดไป",
                            "sLast": "สุดท้าย"
                        }
                    }
                });
                // append buttons container if buttons plugin present
                if (dt1.buttons) {
                    dt1.buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
                }
            }
        }

        // example2 init only if present
        if (typeof $.fn.DataTable !== 'undefined' && $('#example2').length && !$.fn.DataTable.isDataTable(
                '#example2')) {
            $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
        }
    });
</script>