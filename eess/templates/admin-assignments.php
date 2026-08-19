<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-assignments-container" dir="rtl" style="font-family: 'Cairo', sans-serif;">

    <!-- Top Action Bar & Homework Search Engine -->
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
        <div style="flex: 1;">
            <input type="text" id="eess-homework-search" onkeyup="eessFilterHomework()" class="sm-input" placeholder="🔍 ابحث عن واجب باسم المادة، المدرس، الطالب، أو العنوان..." style="height: 40px; border-radius: 8px; width: 100%; font-size: 13px; padding: 0 15px;">
        </div>
        <button type="button" onclick="document.getElementById('add-assignment-modal').style.display='flex'" class="sm-btn" style="height: 40px; padding: 0 20px; font-weight: 800; font-size: 13px; background: var(--sm-primary-color); color: white !important; display: inline-flex; align-items: center; gap: 8px; border-radius: 8px; cursor: pointer;">
            <span class="dashicons dashicons-plus-alt" style="font-size: 16px; width: 16px; height: 16px; margin: 0;"></span>
            <span>إضافة واجب جديد</span>
        </button>
    </div>

    <div class="sm-tabs-wrapper" style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #eee;">
        <button class="sm-tab-btn sm-active" onclick="smOpenInternalTab('received-assignments', this)">الواجبات المستلمة</button>
        <button class="sm-tab-btn" onclick="smOpenInternalTab('sent-assignments', this)">الواجبات المرسلة</button>
    </div>

    <!-- TAB 1: RECEIVED ASSIGNMENTS -->
    <div id="received-assignments" class="sm-internal-tab">
        <div class="sm-table-container">
            <table class="sm-table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>من</th>
                        <th>العنوان والمادة</th>
                        <th>المرفقات</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $received = SM_DB::get_assignments($user->ID, 'assignment');
                    if (empty($received)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 35px; color: #94a3b8;">لا توجد أي واجبات مستلمة حتى الآن.</td></tr>
                    <?php else: foreach($received as $a): ?>
                        <tr>
                            <td style="font-size: 12px; font-weight: 600;"><?php echo date('Y-m-d', strtotime($a->created_at)); ?></td>
                            <td style="font-weight: 700; color: #334155;"><?php echo esc_html($a->sender_name); ?></td>
                            <td style="font-weight: 700; color: #0f172a;"><?php echo esc_html($a->title); ?></td>
                            <td>
                                <?php if ($a->file_url): ?>
                                    <a href="<?php echo esc_url($a->file_url); ?>" target="_blank" class="sm-btn sm-btn-outline" style="font-size: 11px; padding: 4px 10px; border-radius: 6px;">📄 فتح المستند</a>
                                <?php else: ?>
                                    <span style="color: #cbd5e1;">---</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick='viewAssignment(<?php echo json_encode($a); ?>)' class="sm-btn sm-btn-outline" style="font-size: 11px; padding: 4px 12px; border-radius: 6px;">عرض التفاصيل</button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 2: SENT ASSIGNMENTS -->
    <div id="sent-assignments" class="sm-internal-tab" style="display: none;">
        <div class="sm-table-container">
            <table class="sm-table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>إلى (المستلم)</th>
                        <th>العنوان والمادة</th>
                        <th>المرفقات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sent = SM_DB::get_sent_assignments($user->ID);
                    if (empty($sent)): ?>
                        <tr><td colspan="4" style="text-align: center; padding: 35px; color: #94a3b8;">لم تقم بإرسال أي واجبات مسجلة بعد.</td></tr>
                    <?php else: foreach($sent as $a): ?>
                        <tr>
                            <td style="font-size: 12px; font-weight: 600;"><?php echo date('Y-m-d', strtotime($a->created_at)); ?></td>
                            <td style="font-weight: 700; color: #334155;"><?php echo esc_html($a->receiver_name); ?></td>
                            <td style="font-weight: 700; color: #0f172a;"><?php echo esc_html($a->title); ?></td>
                            <td>
                                <?php if ($a->file_url): ?>
                                    <a href="<?php echo esc_url($a->file_url); ?>" target="_blank" class="sm-btn sm-btn-outline" style="font-size: 11px; padding: 4px 10px; border-radius: 6px;">📄 فتح المستند</a>
                                <?php else: ?>
                                    <span style="color: #cbd5e1;">---</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- UPGRADED MULTI-STUDENT HOMEWORK MODAL -->
<div id="add-assignment-modal" class="sm-modal-overlay" style="display: none; align-items: center; justify-content: center; z-index: 99999; background: rgba(15,23,42,0.7); backdrop-filter: blur(4px);">
    <div class="sm-modal-content" style="max-width: 680px; width: 100%; border-radius: 12px; padding: 20px; font-family: 'Cairo', sans-serif;">
        <div class="sm-modal-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <span class="dashicons dashicons-clipboard" style="color: var(--sm-primary-color);"></span>
                <span>إضافة وإسناد واجب مدرسي جديد</span>
            </h3>
            <button type="button" class="sm-modal-close" onclick="document.getElementById('add-assignment-modal').style.display='none'" style="background: none; border: none; font-size: 22px; cursor: pointer;">&times;</button>
        </div>

        <form id="add-assignment-form">
            <?php wp_nonce_field('sm_assignment_action', 'sm_nonce'); ?>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="sm-form-group" style="margin-bottom:0;">
                    <label class="sm-label" style="font-weight: 700; font-size: 11px;">عنوان الواجب المدرسي <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="title" class="sm-input" placeholder="مثال: واجب الدرس الأول - حل تدريبات الصفحة 24" required style="height: 36px; font-size: 12px;">
                </div>
                <div class="sm-form-group" style="margin-bottom:0;">
                    <label class="sm-label" style="font-weight: 700; font-size: 11px;">تاريخ التسليم المتوقع</label>
                    <input type="date" name="due_date" class="sm-input" value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>" style="height: 36px; font-size: 12px;">
                </div>
            </div>

            <!-- Student Search Engine & Multi-Student Selection -->
            <div class="sm-form-group" style="position: relative; margin-bottom: 12px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <label class="sm-label" style="font-weight: 700; font-size: 11px; margin-bottom: 5px; display: block;">تحديد الطلاب المستهدفين بالواجب (محرك البحث المباشر): <span style="color:#ef4444;">*</span></label>
                <input type="text" id="hw_student_search_input" class="sm-input" placeholder="ابحث باسم الطالب أو رقم الكود لإضافته..." autocomplete="off" style="height: 36px; font-size: 12px;">
                <div id="hw_search_dropdown" style="display:none; position:absolute; top:100%; left:12px; right:12px; background:white; border:1px solid #cbd5e1; border-radius:0 0 8px 8px; z-index:1000; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); max-height:200px; overflow-y:auto;">
                    <!-- Dropdown items via AJAX -->
                </div>
                <div id="hw_selected_students_container" style="display:flex; flex-wrap:wrap; gap:6px; margin-top:8px;">
                    <!-- Selected student tag badges -->
                </div>
                <input type="hidden" name="receiver_ids" id="hw_receiver_ids" required>
                <span class="eess-field-error" id="err_hw_receivers" style="display:none; color:#dc2626; font-size:10px; font-weight:bold; margin-top:3px;">يرجى اختيار طالب واحد على الأقل لإرسال الواجب.</span>
            </div>

            <div class="sm-form-group" style="margin-bottom: 12px;">
                <label class="sm-label" style="font-weight: 700; font-size: 11px;">شرح وتفاصيل الواجب والتعليمات:</label>
                <textarea name="description" class="sm-input" placeholder="اكتب التعليمات والتوجيهات المطلوبة من الطلاب بالتفصيل..." style="height: 70px; font-size: 12px; padding: 8px;"></textarea>
            </div>

            <div class="sm-form-group" style="margin-bottom: 15px;">
                <label class="sm-label" style="font-weight: 700; font-size: 11px;">إرفاق مستند أو ورقة عمل (PDF / صورة / رابط):</label>
                <div style="display:flex; gap:8px;">
                    <input type="text" name="file_url" id="assignment_file_url" class="sm-input" placeholder="أدخل رابط المستند أو اضغط رفع مرفق..." style="height: 36px; font-size: 12px; flex:1;">
                    <button type="button" onclick="smOpenMediaUploader('assignment_file_url')" class="sm-btn" style="width:auto; font-size:12px; font-weight:700; background:#334155; color: white !important;">رفع ملف</button>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 12px;">
                <button type="button" onclick="document.getElementById('add-assignment-modal').style.display='none'" class="sm-btn sm-btn-outline" style="height: 36px; padding: 0 16px; font-size: 12px;">إلغاء</button>
                <button type="submit" id="hw_submit_btn" class="sm-btn" style="height: 36px; padding: 0 24px; font-weight: 800; font-size: 12px; background: var(--sm-primary-color); color: white !important;">إرسال الواجب الآن ➔</button>
            </div>
        </form>
    </div>
</div>

<script>
let hwSelectedStudents = [];

(function() {
    let hwSearchTimer;
    const searchInput = document.getElementById('hw_student_search_input');

    document.addEventListener('click', function(e) {
        if (searchInput && !searchInput.contains(e.target)) {
            const dropdown = document.getElementById('hw_search_dropdown');
            if (dropdown) dropdown.style.display = 'none';
        }
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(hwSearchTimer);
            if (query.length < 2) {
                document.getElementById('hw_search_dropdown').style.display = 'none';
                return;
            }

            hwSearchTimer = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'sm_search_students');
                formData.append('query', query);

                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const results = res.data;
                        const dropdown = document.getElementById('hw_search_dropdown');
                        dropdown.innerHTML = '';
                        if (results.length === 0) {
                            dropdown.innerHTML = '<div style="padding:10px; color:#666; text-align:center;">لم يتم العثور على طلاب مطابقتين.</div>';
                        } else {
                            results.forEach(s => {
                                const div = document.createElement('div');
                                div.style = "padding:8px 12px; border-bottom:1px solid #f1f5f9; cursor:pointer; display:flex; align-items:center; gap:8px; font-size:12px;";
                                div.onmouseover = () => div.style.background = '#f8fafc';
                                div.onmouseout = () => div.style.background = '#fff';
                                div.innerHTML = `
                                    <strong style="color:#0f172a;">${s.name}</strong>
                                    <span style="font-size:10px; color:#64748b;">(الصف: ${s.class_name || ''} ${s.section || ''})</span>
                                `;
                                div.onclick = () => selectHwStudent(s);
                                dropdown.appendChild(div);
                            });
                        }
                        dropdown.style.display = 'block';
                    }
                });
            }, 300);
        });
    }

    const form = document.getElementById('add-assignment-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (hwSelectedStudents.length === 0) {
                document.getElementById('err_hw_receivers').style.display = 'block';
                return;
            } else {
                document.getElementById('err_hw_receivers').style.display = 'none';
            }

            const btn = document.getElementById('hw_submit_btn');
            btn.innerText = 'جاري إرسال الواجب...';
            btn.disabled = true;

            const formData = new FormData(this);
            formData.append('action', 'sm_add_assignment_ajax');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert('✅ ' + (res.data.message || 'تم إرسال الواجب بنجاح.'));
                    location.reload();
                } else {
                    alert('❌ خطأ: ' + (res.data || 'فشل في إرسال الواجب.'));
                    btn.innerText = 'إرسال الواجب الآن ➔';
                    btn.disabled = false;
                }
            });
        });
    }
})();

function selectHwStudent(s) {
    if (hwSelectedStudents.find(x => x.id === s.id)) return;
    hwSelectedStudents.push(s);
    renderHwSelectedStudents();
    document.getElementById('hw_student_search_input').value = '';
    document.getElementById('hw_search_dropdown').style.display = 'none';
}

function renderHwSelectedStudents() {
    const container = document.getElementById('hw_selected_students_container');
    container.innerHTML = '';
    const ids = [];

    hwSelectedStudents.forEach(s => {
        // Use parent_user_id or user ID
        const targetId = s.parent_user_id || s.id;
        ids.push(targetId);

        const tag = document.createElement('div');
        tag.style = "background:#e0f2fe; padding:3px 10px; border-radius:20px; border:1px solid #bae6fd; display:flex; align-items:center; gap:6px; font-size:11px; font-weight:700; color:#0369a1;";
        tag.innerHTML = `
            <span>${s.name} (${s.class_name || ''})</span>
            <span onclick="removeHwStudent(${s.id})" style="cursor:pointer; color:#e53e3e; font-weight:bold;">✖</span>
        `;
        container.appendChild(tag);
    });

    document.getElementById('hw_receiver_ids').value = ids.join(',');
    if (ids.length > 0) {
        document.getElementById('err_hw_receivers').style.display = 'none';
    }
}

function removeHwStudent(id) {
    hwSelectedStudents = hwSelectedStudents.filter(x => x.id !== id);
    renderHwSelectedStudents();
}

function viewAssignment(a) {
    alert("تفاصيل الواجب:\n\n" + (a.description || 'لا توجد تعليمات إضافية.'));
}

function eessFilterHomework() {
    const q = document.getElementById('eess-homework-search').value.trim().toLowerCase();
    const rows = document.querySelectorAll('.sm-table tbody tr');

    rows.forEach(row => {
        if (row.cells.length < 2) return;
        const text = row.textContent.toLowerCase();
        if (text.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
