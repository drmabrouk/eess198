<?php
if (!defined('ABSPATH')) exit;

/**
 * Official Lesson Preparation Document Template (EESS)
 * High-quality printable PDF document dynamically adapting to teacher's subject fields.
 */

$data = json_decode($prep->lesson_data, true) ?: array();
$teacher = get_userdata($prep->teacher_id);
$teacher_name = $teacher ? $teacher->display_name : 'غير محدد';
$emp_id = get_user_meta($prep->teacher_id, 'sm_employee_id', true) ?: (get_user_meta($prep->teacher_id, 'sm_employee_code', true) ?: $prep->teacher_id);
$school_info = SM_Settings::get_school_info();

// Dynamic subject-specific field labels
$fields = SM_Settings::get_subject_lesson_fields($prep->subject);
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>وثيقة إعداد وتحضير الدرس المعتمدة - <?php echo esc_html($prep->title); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Cairo', Arial, sans-serif;
            padding: 25px;
            color: #0f172a;
            background: #ffffff;
            line-height: 1.6;
            direction: rtl;
            text-align: right;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .doc-header {
            border-bottom: 3px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .doc-title {
            font-size: 20px;
            font-weight: 900;
            margin: 0;
            color: #0f172a;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .meta-table th, .meta-table td {
            border: 1px solid #cbd5e1;
            padding: 9px 12px;
            text-align: right;
            font-size: 12.5px;
        }
        .meta-table th {
            background: #f8fafc;
            font-weight: bold;
            width: 22%;
            color: #334155;
        }
        .section-card {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .section-card-title {
            font-size: 14px;
            font-weight: 800;
            color: #2563eb;
            margin: 0 0 8px 0;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .section-card-body {
            font-size: 12.5px;
            color: #334155;
            white-space: pre-line;
            line-height: 1.6;
            min-height: 35px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 11px;
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
        <span style="font-weight: 700; font-size: 13px; color: #334155;">وثيقة تحضير رسمية جاهزة للطباعة والتصدير كملف PDF مع الحقول المخصصة للمادة</span>
        <button onclick="window.print()" style="padding: 8px 18px; background: #0f172a; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 13px;">🖨️ بدء طباعة الوثيقة / حفظ PDF</button>
    </div>

    <!-- Official Header -->
    <div class="doc-header">
        <div>
            <h1 class="doc-title"><?php echo esc_html($school_info['school_name'] ?? 'خدمات الأنظمة الإلكترونية التعليمية (EESS)'); ?></h1>
            <p style="margin: 4px 0 0 0; color: #64748b; font-size: 12px; font-weight: 700;">وثيقة تحضير وإعداد درس معتمدة | تاريخ التصدير: <?php echo current_time('Y-m-d H:i'); ?></p>
        </div>
        <div style="text-align: left;">
            <div style="font-weight: 900; font-size: 18px; color: #2563eb;">EESS ONLINE</div>
            <div style="font-size: 11px; color: #64748b;">منظومة تحضير الدروس الرقمية</div>
        </div>
    </div>

    <!-- Metadata Grid Table -->
    <table class="meta-table">
        <tr>
            <th>اسم المعلم المعدّ:</th>
            <td><?php echo esc_html($teacher_name); ?></td>
            <th>الرقم الوظيفي (ID):</th>
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
                    echo '<span class="status-badge status-approved">✓ معتمد رسمياً من المشرف</span>';
                } elseif ($prep->status === 'submitted' || $prep->status === 'late') {
                    echo '<span class="status-badge status-submitted">مقدم ومحفوظ بنجاح</span>';
                } else {
                    echo '<span class="status-badge status-draft">مسودة قيد الإعداد</span>';
                }
                ?>
            </td>
        </tr>
    </table>

    <!-- Subject Specific Dynamic Fields -->
    <div class="section-card">
        <h3 class="section-card-title">1. <?php echo esc_html($fields['label1']); ?></h3>
        <div class="section-card-body"><?php echo esc_html(!empty($data['objectives']) ? $data['objectives'] : 'غير مسجل'); ?></div>
    </div>

    <div class="section-card">
        <h3 class="section-card-title">2. <?php echo esc_html($fields['label2']); ?></h3>
        <div class="section-card-body"><?php echo esc_html(!empty($data['warmup']) ? $data['warmup'] : 'غير مسجل'); ?></div>
    </div>

    <div class="section-card">
        <h3 class="section-card-title">3. <?php echo esc_html($fields['label3']); ?></h3>
        <div class="section-card-body"><?php echo esc_html(!empty($data['activities']) ? $data['activities'] : 'غير مسجل'); ?></div>
    </div>

    <div class="section-card">
        <h3 class="section-card-title">4. <?php echo esc_html($fields['label4']); ?></h3>
        <div class="section-card-body"><?php echo esc_html(!empty($data['evaluation']) ? $data['evaluation'] : 'غير مسجل'); ?></div>
    </div>

    <div class="section-card">
        <h3 class="section-card-title">5. الواجبات والمهام المقررة</h3>
        <div class="section-card-body"><?php echo esc_html(!empty($data['homework']) ? $data['homework'] : 'لا يوجد واجب مقرر لهذا الدرس'); ?></div>
    </div>

    <div class="section-card">
        <h3 class="section-card-title">6. الملاحظات والتأملات التربوية / إرشادات السلامة والتوجيهات</h3>
        <div class="section-card-body"><?php echo esc_html(!empty($data['notes']) ? $data['notes'] : 'لا توجد ملاحظات إضافية مسجلة'); ?></div>
    </div>

</body>
</html>
