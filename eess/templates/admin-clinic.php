<?php
if (!defined('ABSPATH')) exit;

$user = wp_get_current_user();
$roles = (array) $user->roles;
$is_staff_who_can_send = in_array('administrator', $roles) || current_user_can('manage_options') || in_array('sm_system_admin', $roles) || in_array('sm_principal', $roles) || in_array('sm_supervisor', $roles);
$is_clinic_staff = in_array('sm_clinic', $roles) || in_array('administrator', $roles) || in_array('sm_system_admin', $roles) || in_array('sm_principal', $roles) || in_array('sm_supervisor', $roles);

global $wpdb;

// Fetch pending referrals (arrival not confirmed)
$pending_referrals = $wpdb->get_results("
    SELECT c.*, s.name as student_name, s.class_name, s.section, u.display_name as referrer_name
    FROM {$wpdb->prefix}sm_clinic c
    JOIN {$wpdb->prefix}sm_students s ON c.student_id = s.id
    JOIN {$wpdb->prefix}users u ON c.referrer_id = u.ID
    WHERE c.arrival_confirmed = 0
    ORDER BY c.created_at DESC
");

// Fetch history (arrival confirmed)
$history = $wpdb->get_results("
    SELECT c.*, s.name as student_name, s.class_name, s.section, u.display_name as referrer_name
    FROM {$wpdb->prefix}sm_clinic c
    JOIN {$wpdb->prefix}sm_students s ON c.student_id = s.id
    JOIN {$wpdb->prefix}users u ON c.referrer_id = u.ID
    WHERE c.arrival_confirmed = 1
    ORDER BY c.created_at DESC
    LIMIT 100
");
?>

<div class="sm-clinic-module" dir="rtl">

    <!-- Clinic Search Engine -->
    <div style="margin-bottom: 25px;">
        <input type="text" id="eess-clinic-search" onkeyup="eessFilterClinic()" class="sm-input" placeholder="🔍 ابحث عن طالب بالاسم، الصف، أو المحول..." style="height: 42px; border-radius: 8px; width: 100%; font-family: 'Cairo'; padding: 0 15px;">
    </div>

    <!-- PENDING REFERRALS -->
    <div style="margin-bottom: 40px;">
        <h4 style="padding-bottom: 5px; margin-bottom: 15px; font-weight: 800; color: #1e293b;">الطلاب المحولون (بانتظار الوصول)</h4>
        <?php if (empty($pending_referrals)): ?>
            <div style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 12px; color: var(--sm-text-gray);">لا يوجد طلاب محولون حالياً.</div>
        <?php else: ?>
            <div class="sm-table-container">
                <table class="sm-table">
                    <thead>
                        <tr>
                            <th>الوقت</th>
                            <th>الطالب</th>
                            <th>المحول</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_referrals as $r): ?>
                            <tr>
                                <td><?php echo date('H:i', strtotime($r->created_at)); ?></td>
                                <td>
                                    <div style="font-weight: 800;"><?php echo esc_html($r->student_name); ?></div>
                                    <div style="font-size: 11px; color: var(--sm-text-gray);"><?php echo $r->class_name . ' ' . $r->section; ?></div>
                                </td>
                                <td style="font-weight: 700;"><?php echo esc_html($r->referrer_name); ?></td>
                                <td>
                                    <?php if ($is_clinic_staff): ?>
                                        <button onclick="confirmClinicArrival(<?php echo $r->id; ?>)" class="sm-btn" style="background: #38a169; font-size: 11px; padding: 5px 12px;">تأكيد الوصول</button>
                                    <?php else: ?>
                                        <span class="sm-badge" style="background: #edf2f7; color: #4a5568;">بانتظار الوصول</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- HISTORY -->
    <div>
        <h4 style="padding-bottom: 5px; margin-bottom: 15px; font-weight: 800; color: #1e293b;">سجل الزيارات اليومية</h4>
        <?php if (empty($history)): ?>
            <div style="padding: 40px; text-align: center; background: #f8fafc; border-radius: 12px; color: var(--sm-text-gray);">لا يوجد سجلات سابقة.</div>
        <?php else: ?>
            <div class="sm-table-container">
                <table class="sm-table">
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>وقت الوصول</th>
                            <th>الحالة</th>
                            <th>الإجراء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 800;"><?php echo esc_html($h->student_name); ?></div>
                                    <div style="font-size: 11px; color: var(--sm-text-gray);"><?php echo $h->class_name . ' ' . $h->section; ?></div>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($h->arrival_at)); ?></td>
                                <td style="max-width: 200px; font-size: 12px;"><?php echo esc_html($h->health_condition); ?></td>
                                <td style="max-width: 200px; font-size: 12px;"><?php echo esc_html($h->action_taken); ?></td>
                                <td>
                                    <?php if ($is_clinic_staff): ?>
                                        <button onclick="openClinicEditModal(<?php echo htmlspecialchars(json_encode($h)); ?>)" class="sm-btn sm-btn-outline" style="padding: 5px;"><span class="dashicons dashicons-edit"></span></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Referral Modal -->
<div id="referral-modal" class="sm-modal-overlay">
    <div class="sm-modal-content" style="max-width: 500px;">
        <div class="sm-modal-header">
            <h3>تحويل طالب للعيادة</h3>
            <button class="sm-modal-close" onclick="document.getElementById('referral-modal').style.display='none'">&times;</button>
        </div>
        <div class="sm-form-group">
            <label class="sm-label">البحث عن الطالب:</label>
            <input type="text" id="clinic-student-search" class="sm-input" placeholder="اكتب اسم الطالب أو كوده..." onkeyup="clinicSearchStudents(this.value)">
            <div id="clinic-search-results" style="background: #fff; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; display: none;"></div>
        </div>
        <div id="selected-student-box" style="display: none; background: #f0fdf4; padding: 15px; border-radius: 8px; border: 1px solid #bbf7d0; margin-bottom: 20px;">
            <div style="font-weight: 800;" id="selected-student-name"></div>
            <div style="font-size: 12px; color: #166534;" id="selected-student-info"></div>
            <input type="hidden" id="selected-student-id">
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button onclick="submitClinicReferral()" class="sm-btn" style="background: var(--sm-primary-color);">إرسال للعيادة</button>
            <button onclick="document.getElementById('referral-modal').style.display='none'" class="sm-btn sm-btn-outline">إلغاء</button>
        </div>
    </div>
</div>

<!-- Clinic Record Edit Modal -->
<div id="clinic-edit-modal" class="sm-modal-overlay">
    <div class="sm-modal-content" style="max-width: 550px;">
        <div class="sm-modal-header">
            <h3>تحديث السجل الصحي</h3>
            <button class="sm-modal-close" onclick="document.getElementById('clinic-edit-modal').style.display='none'">&times;</button>
        </div>
        <input type="hidden" id="edit-referral-id">
        <div class="sm-form-group">
            <label class="sm-label">الحالة الصحية / الشكوى:</label>
            <textarea id="edit-health-condition" class="sm-textarea" rows="3"></textarea>
        </div>
        <div class="sm-form-group">
            <label class="sm-label">الإجراء المتخذ / العلاج:</label>
            <textarea id="edit-action-taken" class="sm-textarea" rows="3"></textarea>
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button onclick="submitClinicUpdate()" class="sm-btn" style="background: #38a169;">حفظ السجل</button>
            <button onclick="document.getElementById('clinic-edit-modal').style.display='none'" class="sm-btn sm-btn-outline">إلغاء</button>
        </div>
    </div>
</div>

<script>
function toggleClinicReportDropdown() {
    const menu = document.getElementById('clinic-report-menu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function clinicSearchStudents(query) {
    if (query.length < 2) {
        document.getElementById('clinic-search-results').style.display = 'none';
        return;
    }

    const formData = new FormData();
    formData.append('action', 'sm_search_students');
    formData.append('query', query);

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success && res.data.length > 0) {
            let html = '';
            res.data.forEach(s => {
                html += `<div style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;" onclick="selectClinicStudent(${s.id}, '${s.name}', '${s.class_name} ${s.section}')">
                            <strong>${s.name}</strong> (${s.student_code})<br><small>${s.class_name} - ${s.section}</small>
                         </div>`;
            });
            document.getElementById('clinic-search-results').innerHTML = html;
            document.getElementById('clinic-search-results').style.display = 'block';
        }
    });
}

function selectClinicStudent(id, name, info) {
    document.getElementById('selected-student-id').value = id;
    document.getElementById('selected-student-name').innerText = name;
    document.getElementById('selected-student-info').innerText = info;
    document.getElementById('selected-student-box').style.display = 'block';
    document.getElementById('clinic-search-results').style.display = 'none';
    document.getElementById('clinic-student-search').value = '';
}

function submitClinicReferral() {
    const id = document.getElementById('selected-student-id').value;
    if (!id) { alert('يرجى اختيار طالب أولاً'); return; }

    const formData = new FormData();
    formData.append('action', 'sm_add_clinic_referral');
    formData.append('student_id', id);
    formData.append('nonce', '<?php echo wp_create_nonce("sm_clinic_action"); ?>');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            smShowNotification('تم التحويل للعيادة بنجاح');
            setTimeout(() => location.reload(), 500);
        }
    });
}

function confirmClinicArrival(referralId) {
    const formData = new FormData();
    formData.append('action', 'sm_confirm_clinic_arrival');
    formData.append('referral_id', referralId);
    formData.append('nonce', '<?php echo wp_create_nonce("sm_clinic_action"); ?>');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            smShowNotification('تم تأكيد وصول الطالب');
            setTimeout(() => location.reload(), 500);
        }
    });
}

function openClinicEditModal(data) {
    document.getElementById('edit-referral-id').value = data.id;
    document.getElementById('edit-health-condition').value = data.health_condition || '';
    document.getElementById('edit-action-taken').value = data.action_taken || '';
    document.getElementById('clinic-edit-modal').style.display = 'flex';
}

function submitClinicUpdate() {
    const id = document.getElementById('edit-referral-id').value;
    const cond = document.getElementById('edit-health-condition').value;
    const act = document.getElementById('edit-action-taken').value;

    const formData = new FormData();
    formData.append('action', 'sm_update_clinic_record');
    formData.append('referral_id', id);
    formData.append('health_condition', cond);
    formData.append('action_taken', act);
    formData.append('nonce', '<?php echo wp_create_nonce("sm_clinic_action"); ?>');

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            smShowNotification('تم تحديث السجل بنجاح');
            setTimeout(() => location.reload(), 500);
        }
    });
}

document.addEventListener('click', function(e) {
    const results = document.getElementById('clinic-search-results');
    if (results && !results.contains(e.target) && e.target.id !== 'clinic-student-search') {
        results.style.display = 'none';
    }
});

function eessFilterClinic() {
    const q = document.getElementById('eess-clinic-search').value.trim().toLowerCase();
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
