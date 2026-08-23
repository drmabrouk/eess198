<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$user_id = get_current_user_id();

$roles_list = array(
    'sm_system_admin'              => 'مدير النظام',
    'sm_principal'                 => 'مدير المدرسة',
    'sm_supervisor'                => 'المشرف التربوي',
    'sm_discipline_supervisor'     => 'مشرف الانضباط السلوكي',
    'sm_activities_supervisor'     => 'مشرف الأنشطة',
    'sm_transportation_supervisor' => 'مشرف النقل والمواصلات',
    'sm_coordinator'               => 'منسق المادة',
    'sm_teacher'                   => 'معلم',
    'sm_bus_supervisor'            => 'مشرف الحافلة',
    'sm_parent'                    => 'ولي أمر'
);

$announcements = $wpdb->get_results("SELECT a.*, u.display_name as author_name FROM {$wpdb->prefix}sm_system_announcements a LEFT JOIN {$wpdb->users} u ON a.created_by = u.ID ORDER BY a.created_at DESC");

// User tracking activity query
$activity_logs = $wpdb->get_results("SELECT ua.*, a.title as announcement_title, u.display_name, u.user_login FROM {$wpdb->prefix}sm_user_announcements ua INNER JOIN {$wpdb->prefix}sm_system_announcements a ON ua.announcement_id = a.id INNER JOIN {$wpdb->users} u ON ua.user_id = u.ID ORDER BY ua.updated_at DESC LIMIT 200");
?>

<div class="sm-container" style="padding: 10px 0; font-family: 'Cairo', sans-serif !important; direction: rtl;">

    <!-- Header Banner -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border-radius: 16px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3); display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h2 style="margin: 0; font-size: 20px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 10px;">
                <span class="dashicons dashicons-mega" style="font-size: 26px; width: 26px; height: 26px; color: #38bdf8;"></span>
                مركز إدارة الإشعارات والتعاميم الإدارية
            </h2>
            <p style="margin: 6px 0 0 0; font-size: 13px; color: #94a3b8;">إنشاء وتخصيص التعاميم، استهداف الرتب، ومتابعة سجلات المشاهدة والإغلاق للمستخدمين</p>
        </div>
    </div>

    <!-- Announcement Creation Form Card -->
    <div style="background: #ffffff; border-radius: 16px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 30px;">
        <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 800; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-plus-alt" style="color: #2563eb;"></span>
            إضافة إعلان أو تعميم إداري جديد
        </h3>

        <form id="eess-announcement-form" onsubmit="eessCreateAnnouncement(event)">
            <?php wp_nonce_field('sm_announcement_action', 'sm_announcement_nonce'); ?>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">عنوان الإشعار / التعميم <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="title" required placeholder="مثال: تنبيه هام بشأن مواعيد الاختبارات النصفية" style="width: 100%; height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">نوع الإشعار / الأيقونة</label>
                    <select name="type" style="width: 100%; height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13px; box-sizing: border-box;">
                        <option value="info">ℹ️ إداري عام (Info)</option>
                        <option value="warning">⚠️ تنبيه هام (Warning)</option>
                        <option value="urgent">🚨 عاجل جداً (Urgent)</option>
                        <option value="success">✅ تحديث جديد (Success)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label style="font-size: 13px; font-weight: 700; color: #334155;">تفاصيل الإشعار (الحد الأقصى 500 حرف) <span style="color:#ef4444;">*</span></label>
                    <span id="char-counter" style="font-size: 12px; font-weight: 700; color: #64748b;">0 / 500</span>
                </div>
                <textarea name="details" id="announcement-details" required maxlength="500" rows="4" placeholder="أدخل التفاصيل والتعليمات الخاصة بالإشعار..." oninput="document.getElementById('char-counter').innerText = this.value.length + ' / 500'" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; font-size: 13px; box-sizing: border-box;"></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 10px;">الرتب المستهدفة بالإشعار <span style="color:#ef4444;">*</span></label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; background: #f8fafc; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <?php foreach ($roles_list as $role_key => $role_name): ?>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #334155; cursor: pointer;">
                            <input type="checkbox" name="target_roles[]" value="<?php echo esc_attr($role_key); ?>" checked style="width: 16px; height: 16px;">
                            <span><?php echo esc_html($role_name); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">مدة العرض التلقائي (بالثواني)</label>
                    <input type="number" name="display_duration" value="10" min="3" max="60" style="width: 100%; height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">عدد مرات الظهور التلقائي لكل مستخدم</label>
                    <input type="number" name="display_frequency" value="1" min="1" max="10" style="width: 100%; height: 42px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 13px; box-sizing: border-box;">
                </div>
            </div>

            <div id="create-msg-status" style="display: none; margin-bottom: 15px; padding: 12px; border-radius: 8px; font-size: 13px; font-weight: 700;"></div>

            <button type="submit" id="btn-save-announcement" class="sm-btn" style="width: 220px; height: 44px; background: #2563eb; color: white; font-weight: 800; font-size: 14px; border-radius: 10px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <span class="dashicons dashicons-send"></span> نشر واعتماد الإشعار
            </button>
        </form>
    </div>

    <!-- Active Announcements Table Card -->
    <div style="background: #ffffff; border-radius: 16px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 30px;">
        <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 800; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-list-view" style="color: #2563eb;"></span>
            سجل الإشعارات المنشورة وإحصائيات القراءة
        </h3>

        <div style="overflow-x: auto;">
            <table class="sm-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; text-align: right; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px;">العنوان</th>
                        <th style="padding: 12px;">الرتب المستهدفة</th>
                        <th style="padding: 12px;">تاريخ النشر</th>
                        <th style="padding: 12px; text-align: center;">المشاهدين</th>
                        <th style="padding: 12px; text-align: center;">المغلقين</th>
                        <th style="padding: 12px; text-align: center;">الحالة</th>
                        <th style="padding: 12px; text-align: center;">إجراءات (Actions)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($announcements)): ?>
                        <tr><td colspan="7" style="padding: 20px; text-align: center; color: #94a3b8;">لا توجد إشعارات منشورة حالياً.</td></tr>
                    <?php else: ?>
                        <?php foreach ($announcements as $anc):
                            $target_arr = json_decode($anc->target_roles, true) ?: array();
                            $target_labels = array_map(function($r) use ($roles_list) { return $roles_list[$r] ?? $r; }, $target_arr);

                            $viewed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}sm_user_announcements WHERE announcement_id = %d AND status IN ('viewed', 'closed')", $anc->id));
                            $closed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}sm_user_announcements WHERE announcement_id = %d AND status = 'closed'", $anc->id));
                            $is_active = ($anc->status === 'active');
                        ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px; font-weight: 700; color: #0f172a;"><?php echo esc_html($anc->title); ?></td>
                                <td style="padding: 12px; font-size: 11px; color: #475569;"><?php echo esc_html(implode('، ', $target_labels)); ?></td>
                                <td style="padding: 12px; font-size: 12px; color: #64748b;"><?php echo esc_html($anc->created_at); ?></td>
                                <td style="padding: 12px; text-align: center;"><span style="background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 50px; font-weight: 800; font-size: 11px;"><?php echo $viewed_count; ?></span></td>
                                <td style="padding: 12px; text-align: center;"><span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 50px; font-weight: 800; font-size: 11px;"><?php echo $closed_count; ?></span></td>
                                <td style="padding: 12px; text-align: center;">
                                    <?php if ($is_active): ?>
                                        <span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 50px; font-weight: 800; font-size: 11px;">نشط (Active)</span>
                                    <?php else: ?>
                                        <span style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 50px; font-weight: 800; font-size: 11px;">معطل (Disabled)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <?php if ($is_active): ?>
                                        <button type="button" onclick="eessDisableAnnouncement(<?php echo $anc->id; ?>)" style="background: #ef4444; color: white; border: none; border-radius: 6px; padding: 5px 12px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="dashicons dashicons-no-alt" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                            تعطيل (Disable)
                                        </button>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: #94a3b8; font-weight: 700;">معطل</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed User Audit Log & Show Again Action Card -->
    <div style="background: #ffffff; border-radius: 16px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 800; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
            <span class="dashicons dashicons-admin-users" style="color: #2563eb;"></span>
            سجل تفاعل المستخدمين الفردي وإعادة إظهار الإشعار (Show Again)
        </h3>

        <div style="overflow-x: auto;">
            <table class="sm-table" style="width: 100%; border-collapse: collapse; font-size: 12px;">
                <thead>
                    <tr style="background: #f8fafc; text-align: right; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px;">المستخدم</th>
                        <th style="padding: 12px;">الإشعار</th>
                        <th style="padding: 12px; text-align: center;">الحالة</th>
                        <th style="padding: 12px; text-align: center;">عدد المشاهدات</th>
                        <th style="padding: 12px;">تاريخ المشاهدة/الإغلاق</th>
                        <th style="padding: 12px; text-align: center;">إجراءات (Actions)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activity_logs)): ?>
                        <tr><td colspan="6" style="padding: 20px; text-align: center; color: #94a3b8;">لا توجد سجلات تفاعل حتى الآن.</td></tr>
                    <?php else: ?>
                        <?php foreach ($activity_logs as $log): ?>
                            <tr id="user-log-row-<?php echo $log->id; ?>" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px; font-weight: 700; color: #0f172a;"><?php echo esc_html($log->display_name . ' (' . $log->user_login . ')'); ?></td>
                                <td style="padding: 12px; color: #334155; font-weight: 600;"><?php echo esc_html($log->announcement_title); ?></td>
                                <td style="padding: 12px; text-align: center;">
                                    <?php if ($log->status === 'closed'): ?>
                                        <span style="background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 50px; font-weight: 800; font-size: 11px;">مغلق</span>
                                    <?php elseif ($log->status === 'viewed'): ?>
                                        <span style="background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 50px; font-weight: 800; font-size: 11px;">تمت المشاهدة</span>
                                    <?php else: ?>
                                        <span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 50px; font-weight: 800; font-size: 11px;">معلق</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: center; font-weight: 800;"><?php echo intval($log->view_count); ?></td>
                                <td style="padding: 12px; color: #64748b; font-size: 11px;"><?php echo esc_html($log->closed_at ?: ($log->viewed_at ?: $log->updated_at)); ?></td>
                                <td style="padding: 12px; text-align: center;">
                                    <button type="button" onclick="eessResetUserAnnouncement(<?php echo $log->announcement_id; ?>, <?php echo $log->user_id; ?>, <?php echo $log->id; ?>)" style="background: #f59e0b; color: white; border: none; border-radius: 6px; padding: 5px 12px; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <span class="dashicons dashicons-redo" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                        إظهار مجدداً (Show Again)
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function eessCreateAnnouncement(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-announcement');
    const statusBox = document.getElementById('create-msg-status');

    btn.disabled = true;
    btn.innerHTML = '<span class="dashicons dashicons-update spin"></span> جاري النشر...';
    statusBox.style.display = 'none';

    const formData = jQuery('#eess-announcement-form').serialize() + '&action=sm_create_system_announcement';

    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', formData, function(res) {
        btn.disabled = false;
        btn.innerHTML = '<span class="dashicons dashicons-send"></span> نشر واعتماد الإشعار';

        statusBox.style.display = 'block';
        if (res.success) {
            statusBox.style.background = '#f0fdf4';
            statusBox.style.color = '#166534';
            statusBox.innerText = res.data.message || 'تم نشر الإشعار بنجاح!';
            setTimeout(() => location.reload(), 1200);
        } else {
            statusBox.style.background = '#fef2f2';
            statusBox.style.color = '#991b1b';
            statusBox.innerText = res.data || 'حدث خطأ أثناء حفظ الإشعار.';
        }
    });
}

function eessResetUserAnnouncement(ancId, userId, logId) {
    if (!confirm('هل أنت تأكد من إعادة تفعيل ظهور هذا الإشعار لهذا المستخدم بعينه؟')) return;

    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
        action: 'sm_reset_user_announcement',
        announcement_id: ancId,
        user_id: userId,
        nonce: '<?php echo wp_create_nonce('sm_announcement_action'); ?>'
    }, function(res) {
        if (res.success) {
            alert('تم إعادة ضبط الإشعار بنجاح وسيظهر للمستخدم عند تسجيل الدخول القادم.');
            location.reload();
        } else {
            alert(res.data || 'حدث خطأ أثناء تنفيذ الإجراء.');
        }
    });
}

function eessDisableAnnouncement(ancId) {
    if (!confirm('هل أنت تأكد من تعطيل هذا الإشعار؟ سيتم إيقاف ظهوره فوراً لجميع المستخدمين المستهدفين.')) return;

    jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
        action: 'sm_disable_system_announcement',
        announcement_id: ancId,
        nonce: '<?php echo wp_create_nonce('sm_announcement_action'); ?>'
    }, function(res) {
        if (res.success) {
            alert(res.data.message || 'تم تعطيل الإشعار بنجاح.');
            location.reload();
        } else {
            alert(res.data || 'حدث خطأ أثناء تعطيل الإشعار.');
        }
    });
}
</script>
