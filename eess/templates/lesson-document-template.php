<?php
if (!defined('ABSPATH')) exit;

/**
 * Official Lesson Preparation Document Template (EESS)
 * Shared template for print, PDF generation, supervisor review, and historical lesson viewing.
 */

$data = json_decode($prep->lesson_data, true) ?: array();
$teacher = get_userdata($prep->teacher_id);
$teacher_name = $teacher ? $teacher->display_name : 'غير محدد';
$emp_id = get_user_meta($prep->teacher_id, 'sm_employee_id', true) ?: (get_user_meta($prep->teacher_id, 'sm_employee_code', true) ?: $prep->teacher_id);
$school_info = SM_Settings::get_school_info();

$fields = SM_Settings::get_subject_lesson_fields($prep->subject);
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>وثيقة إعداد وتحضير الدرس المعتمدة - <?php echo esc_html($prep->title); ?></title>
    <style>
        body {
            font-family: 'Cairo', Arial, sans-serif;
            padding: 35px;
            color: #0f172a;
            background: #ffffff;
            line-height: 1.6;
            direction: rtl;
            text-align: right;
        }
        .doc-header {
            border-bottom: 3px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .doc-title {
            font-size: 22px;
            font-weight: 900;
            margin: 0;
            color: #0f172a;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .meta-table th, .meta-table td {
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            text-align: right;
            font-size: 13px;
        }
        .meta-table th {
            background: #f8fafc;
            font-weight: bold;
            width: 25%;
            color: #334155;
        }
        .section-title {
            font-size: 16px;
            font-weight: 800;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin: 25px 0 12px 0;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .content-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 15px;
            white-space: pre-line;
            color: #334155;
            min-height: 45px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 12px;
        }
        .status-approved { background: #dcfce7; color: #15803d; }
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .status-draft { background: #fef3c7; color: #92400e; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="background: #f1f5f9; padding: 12px 20px; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; border: 1px solid #cbd5e1;">
        <span style="font-weight: 700; font-size: 13px; color: #334155;">وثيقة تحضير رسمية جاهزة للطباعة والتصدير كملف PDF</span>
        <button onclick="window.print()" style="padding: 8px 18px; background: #0f172a; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 13px;">🖨️ بدء طباعة الوثيقة / حفظ PDF</button>
    </div>

    <div class="doc-header">
        <div>
            <h1 class="doc-title"><?php echo esc_html($school_info['school_name'] ?? 'خدمات الأنظمة الإلكترونية التعليمية (EESS)'); ?></h1>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 12px; font-weight: 700;">وثيقة تحضير وإعداد درس معتمدة | تاريخ التصدير: <?php echo current_time('Y-m-d H:i'); ?></p>
        </div>
        <div style="text-align: left;">
            <div style="font-weight: 900; font-size: 18px; color: #2563eb;">EESS ONLINE</div>
            <div style="font-size: 11px; color: #64748b;">منظومة تحضير الدروس</div>
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <th>اسم المعلم المعدّ:</th>
            <td><?php echo esc_html($teacher_name); ?></td>
            <th>الرقم الوظيفي:</th>
            <td><?php echo esc_html($emp_id); ?></td>
        </tr>
        <tr>
            <th>عنوان الدرس الرئيسي:</th>
            <td><strong><?php echo esc_html($prep->title); ?></strong></td>
            <th>المادة الدراسية:</th>
            <td><?php echo esc_html($prep->subject); ?></td>
        </tr>
        <tr>
            <th>الصف والشعبة:</th>
            <td><?php echo esc_html($prep->grade_level . ' / ' . $prep->class_section); ?></td>
            <th>تاريخ إعطاء الدرس:</th>
            <td><?php echo esc_html($prep->lesson_date); ?></td>
        </tr>
        <tr>
            <th>حالة التوثيق والاعتماد:</th>
            <td colspan="3">
                <?php
                if ($prep->status === 'approved') {
                    echo '<span class="status-badge status-approved">معتمد رسمياً من المشرف</span>';
                } elseif ($prep->status === 'submitted' || $prep->status === 'late') {
                    echo '<span class="status-badge status-submitted">مقدم ومحفوظ بنجاح</span>';
                } else {
                    echo '<span class="status-badge status-draft">مسودة قيد الإعداد</span>';
                }
                ?>
            </td>
        </tr>
    </table>

    <h3 class="section-title">1. <?php echo esc_html($fields['label1']); ?></h3>
    <div class="content-box"><?php echo esc_html($data['objectives'] ?? 'غير مسجل'); ?></div>

    <h3 class="section-title">2. <?php echo esc_html($fields['label2']); ?></h3>
    <div class="content-box"><?php echo esc_html($data['warmup'] ?? 'غير مسجل'); ?></div>

    <h3 class="section-title">3. <?php echo esc_html($fields['label3']); ?></h3>
    <div class="content-box"><?php echo esc_html($data['activities'] ?? 'غير مسجل'); ?></div>

    <h3 class="section-title">4. <?php echo esc_html($fields['label4']); ?></h3>
    <div class="content-box"><?php echo esc_html($data['evaluation'] ?? 'غير مسجل'); ?></div>

    <h3 class="section-title">5. الواجبات والمهام المقررة</h3>
    <div class="content-box"><?php echo esc_html($data['homework'] ?? 'لا يوجد واجب مقرر'); ?></div>

    <h3 class="section-title">6. الملاحظات والتأملات التربوية/إرشادات السلامة</h3>
    <div class="content-box"><?php echo esc_html($data['notes'] ?? 'لا توجد ملاحظات إضافية'); ?></div>

</body>
</html>
