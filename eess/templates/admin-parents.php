<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-content-wrapper" dir="rtl">

    <div style="background: var(--sm-bg-light); padding: 25px; border: 1px solid var(--sm-border-color); border-radius: 8px; margin-bottom: 30px;">
        <form method="get" style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <label class="sm-label">بحث عن ولي أمر (بالاسم أو البريد):</label>
                <input type="text" name="parent_search" class="sm-input" value="<?php echo esc_attr(isset($_GET['parent_search']) ? $_GET['parent_search'] : ''); ?>" placeholder="أدخل بيانات ولي الأمر...">
            </div>
            <div style="display: flex; gap: 10px; align-self: flex-end;">
                <button type="submit" class="sm-btn" style="width:auto;">بحث</button>
                <a href="<?php echo remove_query_arg('parent_search'); ?>" class="sm-btn" style="width:auto; background:var(--sm-text-gray); text-decoration:none;">إعادة ضبط</a>
            </div>
        </form>
    </div>

    <div class="sm-parents-rows-container" style="display: flex; flex-direction: column; gap: 15px;">
        <?php 
        $search = !empty($_GET['parent_search']) ? sanitize_text_field($_GET['parent_search']) : '';
        $args = array('role' => 'sm_parent');

        if ($search) {
            $args['search'] = '*' . $search . '*';
            $args['search_columns'] = array('user_login', 'display_name', 'user_email');

            // Advanced Search: Join with students and check meta
            global $wpdb;
            $extra_parent_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT parent_user_id FROM {$wpdb->prefix}sm_students WHERE (name LIKE %s OR parent_email LIKE %s) AND parent_user_id IS NOT NULL",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            ));

            $phone_parent_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key = 'sm_phone' AND meta_value LIKE %s",
                '%' . $wpdb->esc_like($search) . '%'
            ));

            $all_ids = array_unique(array_merge($extra_parent_ids, $phone_parent_ids));

            if (!empty($all_ids)) {
                // Get users by search first
                $search_parents = get_users($args);
                $search_ids = wp_list_pluck($search_parents, 'ID');

                // Combine and fetch all
                $final_ids = array_unique(array_merge($search_ids, $all_ids));
                unset($args['search'], $args['search_columns']);
                $args['include'] = $final_ids;
            }
        }

        $parents = get_users($args);

        $current_user_scope = EESS_Org_Helper::get_user_scope();
        if (!$current_user_scope['unrestricted']) {
            $parents = array_filter($parents, function($parent) use ($current_user_scope) {
                $children = SM_DB::get_students_by_parent($parent->ID);
                if (empty($children)) return false;
                foreach ($children as $c) {
                    if (in_array(intval($c->school_id), $current_user_scope['schools'])) {
                        return true;
                    }
                }
                return false;
            });
        }

        if (empty($parents)): ?>
            <div style="padding: 60px; text-align: center; background: #fff; border-radius: 12px; border: 1px solid var(--sm-border-color); color: #a0aec0;">
                <span class="dashicons dashicons-admin-users" style="font-size: 48px; width:48px; height:48px; margin-bottom:15px;"></span>
                <p>لا يوجد أولياء أمور مسجلون حالياً.</p>
            </div>
        <?php else: ?>
            <?php foreach ($parents as $parent): 
                $children = SM_DB::get_students_by_parent($parent->ID);
            ?>
                <div class="sm-parent-row" style="background: #fff; border-radius: 12px; border: 1px solid var(--sm-border-color); padding: 20px 30px; display: flex; align-items: center; justify-content: space-between; transition: 0.3s; gap: 20px;">
                    <div style="display: flex; align-items: center; gap: 20px; flex: 2;">
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: #f0f4f8; display: flex; align-items: center; justify-content: center; font-size: 20px;">👨‍👩‍👧</div>
                        <div>
                            <div style="font-weight: 800; color: var(--sm-secondary-color); font-size: 1.1em;"><?php echo esc_html($parent->display_name); ?></div>
                            <div style="font-size: 0.85em; color: #718096; margin-top: 3px;"><?php echo esc_html($parent->user_email); ?></div>
                        </div>
                    </div>

                    <div style="flex: 2; background: #f8fafc; padding: 10px 15px; border-radius: 8px; border: 1px solid #edf2f7; font-size: 0.9em;">
                        <strong>الأبناء:</strong> 
                        <?php if (empty($children)): ?>
                            <span style="color: #e53e3e; font-size: 12px; margin-right: 10px;">لا يوجد أبناء مرتبطين</span>
                        <?php else: ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 5px;">
                                <?php foreach ($children as $c): ?>
                                    <span class="sm-badge sm-badge-low" style="background: #fff; font-size: 11px;"><?php echo esc_html($c->name); ?> (<?php echo SM_Settings::format_grade_name($c->class_name, $c->section, 'short'); ?>)</span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="flex: 1; display: flex; gap: 12px; justify-content: flex-end;">
                        <?php
                            $parent_phone = get_user_meta($parent->ID, 'sm_phone', true);
                            $formatted_phone = SM_Settings::format_uae_phone($parent_phone);
                        ?>
                        <button onclick="requestCallIn(<?php echo $parent->ID; ?>, '<?php echo esc_js($parent->display_name); ?>', '<?php echo esc_js($parent->user_email); ?>', '<?php echo esc_js($formatted_phone ?: ''); ?>')" class="sm-btn" style="background: #F8FAFC; color: #3182CE !important; border: 1px solid #BEE3F8; padding: 6px 15px; font-size: 11px; width: auto; font-weight: 800; box-shadow: none;">
                            <span class="dashicons dashicons-calendar-alt" style="font-size:14px; margin-left:5px;"></span> طلب استدعاء
                        </button>
                        <form method="post" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف حساب ولي الأمر بالكامل؟')">
                            <?php wp_nonce_field('sm_user_action', 'sm_nonce'); ?>
                            <input type="hidden" name="delete_user_id" value="<?php echo $parent->ID; ?>">
                            <button type="submit" name="sm_delete_user" class="sm-btn" style="background: #FFF5F5; color: #E53E3E !important; border: 1px solid #FED7D7; padding: 6px 15px; font-size: 11px; width: auto; font-weight: 800; box-shadow: none;">
                                <span class="dashicons dashicons-trash" style="font-size:14px; margin-left:5px;"></span> حذف
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>


    <div id="add-parent-modal" class="sm-modal-overlay">
        <div class="sm-modal-content">
            <div class="sm-modal-header">
                <h3>إضافة ولي أمر جديد</h3>
                <button class="sm-modal-close" onclick="document.getElementById('add-parent-modal').style.display='none'">&times;</button>
            </div>
            <form id="add-parent-form">
                <?php wp_nonce_field('sm_user_action', 'sm_nonce'); ?>
                <input type="hidden" name="user_role" value="sm_parent">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div class="sm-form-group">
                        <label class="sm-label">الاسم الكامل:</label>
                        <input type="text" name="display_name" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">اسم المستخدم (Login):</label>
                        <input type="text" name="user_login" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">البريد الإلكتروني:</label>
                        <input type="email" name="user_email" class="sm-input" required>
                    </div>
                    <div class="sm-form-group">
                        <label class="sm-label">كلمة المرور:</label>
                        <input type="password" name="user_pass" class="sm-input" required>
                    </div>
                </div>
                <p style="font-size:12px; color:#718096; margin-top:15px;">ملاحظة: لربط ولي الأمر بطالب، قم بتحرير بيانات الطالب من قسم "إدارة الطلاب".</p>
                <button type="submit" class="sm-btn" style="margin-top:20px; width: 100%;">إنشاء الحساب الآن</button>
            </form>
        </div>
    </div>

    <script>
    (function() {
        const addForm = document.getElementById('add-parent-form');
        if (addForm) {
            addForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'sm_add_parent_ajax');
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        smShowNotification('تمت إضافة ولي الأمر');
                        setTimeout(() => location.reload(), 500);
                    }
                });
            });
        }
    })();
    </script>
    <div id="call-in-modal" class="sm-modal-overlay">
        <div class="sm-modal-content" style="max-width: 500px;">
            <div class="sm-modal-header">
                <h3>إرسال طلب استدعاء ولي أمر</h3>
                <button class="sm-modal-close" onclick="document.getElementById('call-in-modal').style.display='none'">&times;</button>
            </div>
            <div style="text-align: center; padding: 20px 0;">
                <p style="font-size: 1.1em; margin-bottom: 25px;">إرسال طلب حضور للمدرسة لولي الأمر:<br><strong id="call_in_parent_name" style="color: var(--sm-primary-color); font-size: 1.2em;"></strong></p>

                <div class="sm-form-group" style="text-align: right;">
                    <label class="sm-label">نص الرسالة المقترح:</label>
                    <textarea id="call_in_msg_text" class="sm-textarea" rows="4">تحية طيبة، نرجو منكم التكرم بزيارة مكتب الإرشاد الطلابي بالمدرسة في أقرب وقت ممكن لمناقشة أمور هامة تخص ابنكم/ابنتكم. شكراً لتعاونكم.</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px;">
                    <button onclick="sendCallViaWhatsApp()" class="sm-btn" style="background: #25D366; gap: 10px;">
                        <span class="dashicons dashicons-whatsapp"></span> واتساب
                    </button>
                    <button onclick="sendCallViaEmail()" class="sm-btn" style="background: #111F35; gap: 10px;">
                        <span class="dashicons dashicons-email"></span> بريد إلكتروني
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let currentParentData = {};

    function requestCallIn(id, name, email, phone) {
        currentParentData = { id, name, email, phone };
        document.getElementById('call_in_parent_name').innerText = name;
        document.getElementById('call-in-modal').style.display = 'flex';
    }

    function sendCallViaWhatsApp() {
        const msg = encodeURIComponent(document.getElementById('call_in_msg_text').value);
        const phone = currentParentData.phone || '';
        if (!phone) {
            alert('رقم الهاتف غير مسجل لهذا الوالد أو صيغته غير صحيحة (يجب أن يكون رقماً إماراتياً).');
            return;
        }
        window.open(`https://wa.me/${phone}?text=${msg}`, '_blank');
    }

    function sendCallViaEmail() {
        const msg = encodeURIComponent(document.getElementById('call_in_msg_text').value);
        const subject = encodeURIComponent('طلب استدعاء رسمي من المدرسة');
        const email = currentParentData.email || '';
        if (!email) {
            alert('البريد الإلكتروني غير مسجل لهذا الوالد.');
            return;
        }
        window.location.href = `mailto:${email}?subject=${subject}&body=${msg}`;
    }
    </script>
</div>
