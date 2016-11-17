<?php
/* For licensing terms, see /license.txt */

/**
 * Script
 * @package chamilo.gradebook
 * @author Julio Montoya - fixes in order to use gradebook models + some code cleaning
 */

$cidReset = true;
$this_section = SECTION_COURSES;
$current_course_tool = TOOL_GRADEBOOK;

api_protect_course_script(true);
api_block_anonymous_users();

if (!api_is_allowed_to_edit()) {
    header('Location: /index.php');
    exit;
}

$my_selectcat = isset($_GET['selectcat']) ? intval($_GET['selectcat']) : '';

if (empty($my_selectcat)) {
    api_not_allowed();
}

$em = Database::getManager();

$course_id = GradebookUtils::get_course_id_by_link_id($my_selectcat);

$tbl_forum_thread = Database:: get_course_table(TABLE_FORUM_THREAD);
$tbl_work = Database:: get_course_table(TABLE_STUDENT_PUBLICATION);
$tbl_attendance = Database:: get_course_table(TABLE_ATTENDANCE);

$table_evaluated = GradebookUtils::getTables();

$submitted = isset($_POST['submitted']) ? $_POST['submitted'] : '';
if ($submitted == 1) {
    Display :: display_confirmation_message(get_lang('GradebookWeightUpdated')) . '<br /><br />';
    if (isset($_POST['evaluation'])) {
        $eval_log = new Evaluation();
    }
}

$output = '';
$my_cat = Category::load($my_selectcat);
$my_cat = $my_cat[0];

$parent_id = $my_cat->get_parent_id();
$parent_cat = Category::load($parent_id);

$my_category = array();
$cat = new Category();
$my_category = $cat->shows_all_information_an_category($my_selectcat);

$original_total = $my_category->getWeight();
$masked_total = $parent_cat[0]->get_weight();

$links = $em
    ->getRepository('ChamiloCoreBundle:GradebookLink')
    ->findBy([
        'categoryId' => $my_selectcat
    ]);
$linksInfo = [];

foreach ($links as $row) {
    $item_weight = $row->getWeight();
    $sql = 'SELECT * FROM '.GradebookUtils::get_table_type_course($row->getType()).'
            WHERE c_id = '.$course_id.' AND '.$table_evaluated[$row->getType()][2].' = '.$row->getRefId();
    $result = Database::query($sql);
    $resource_name = Database::fetch_array($result);

    if (isset($resource_name['lp_type'])) {
        $resource_name = $resource_name[4];
    } else {
        $resource_name = $resource_name[3];
    }
    $linksInfo[] = [
        'id' => $row->getId(),
        'resource_name' => $resource_name
    ];

    // Update only if value changed
    if (isset($_POST['link'][$row->getId()])) {
        $new_weight = trim($_POST['link'][$row->getId()]);
        GradebookUtils::updateLinkWeight(
            $row->getId(),
            $resource_name,
            $new_weight
        );
        $item_weight = $new_weight;
    }

    $output .= '<tr><td>'.GradebookUtils::build_type_icon_tag($row->getType()).'</td>
               <td> '.$resource_name.' '.Display::label(
            $table_evaluated[$row->getType()][3],
            'info'
        ).' </td>';
    $output .= '<td>
                    <input type="hidden" name="link_'.$row->getId().'" value="'.$resource_name.'" />
                    <input size="10" type="text" name="link['.$row->getId().']" value="'.$item_weight.'"/>
               </td></tr>';
}

$evaluations = $em
    ->getRepository('ChamiloCoreBundle:GradebookEvaluation')
    ->findBy([
        'categoryId' => $my_selectcat
    ]);
foreach ($evaluations as $evaluationRow) {
    $item_weight = $evaluationRow->getWeight();
    // update only if value changed
    if (isset($_POST['evaluation'][$evaluationRow->getId()])) {
        $new_weight = trim($_POST['evaluation'][$evaluationRow->getId()]);
        GradebookUtils::updateEvaluationWeight(
            $evaluationRow->getId(),
            $new_weight
        );

        $item_weight = $new_weight;
    }

    $output .= '<tr>
                <td>'.GradebookUtils::build_type_icon_tag('evalnotempty').'</td>
                <td>'.$evaluationRow->getName().' '.Display::label(
            get_lang('Evaluation')
        ).'</td>';
    $output .= '<td>
                    <input type="hidden" name="eval_'.$evaluationRow->getId().'" value="'.$evaluationRow->getName().'" />
                    <input type="text" size="10" name="evaluation['.$evaluationRow->getId().']" value="'.$item_weight.'"/>
                </td></tr>';
}

$my_api_cidreq = api_get_cidreq();
if ($my_api_cidreq == '') {
    $my_api_cidreq = 'cidReq='.$my_category->getCourse()->getCode();
}

$currentUrl = api_get_self().'?'.api_get_cidreq().'&selectcat='.$my_selectcat;

$form = new FormValidator('auto_weight', 'post', $currentUrl);
$form->addHeader(get_lang('AutoWeight'));
$form->addLabel(null, get_lang('AutoWeightExplanation'));
$form->addButtonUpdate(get_lang('AutoWeight'));

if ($form->validate()) {
    $itemCount = count($links) + count($evaluations);
    $weight = 0;
    if ($itemCount > 0) {
        $weight = round($original_total / $itemCount, 2);
    }
    $total = $weight * $itemCount;

    $diff = null;
    if ($original_total !== $total) {
        if ($total > $original_total) {
            $diff = $total - $original_total;
        }
    }

    $total = 0;
    $diffApplied = false;

    foreach ($linksInfo as $link) {
        $weightToApply = $weight;
        if ($diffApplied == false) {
            if (!empty($diff)) {
                $weightToApply = $weight - $diff;
                $diffApplied = true;
            }
        }
        GradebookUtils::updateLinkWeight(
            $link['id'],
            $link['resource_name'],
            $weightToApply
        );
    }

    foreach ($evaluations as $evaluation) {
        $weightToApply = $weight;
        if ($diffApplied == false) {
            if (!empty($diff)) {
                $weightToApply = $weight - $diff;
                $diffApplied = true;
            }
        }
        GradebookUtils::updateEvaluationWeight(
            $evaluation['id'],
            $weightToApply
        );
    }

    header('Location:'.$currentUrl);
    exit;
}


// 	DISPLAY HEADERS AND MESSAGES
if (!isset($_GET['exportpdf']) and !isset($_GET['export_certificate'])) {
    if (isset ($_GET['studentoverview'])) {
        $interbreadcrumb[] = array(
            'url' => Security::remove_XSS(
                    $_SESSION['gradebook_dest']
                ).'?selectcat='.$my_selectcat,
            'name' => get_lang('Gradebook'),
        );
        Display:: display_header(get_lang('FlatView'));
    } elseif (isset ($_GET['search'])) {
        $interbreadcrumb[] = array(
            'url' => Security::remove_XSS(
                    $_SESSION['gradebook_dest']
                ).'?selectcat='.$my_selectcat,
            'name' => get_lang('Gradebook'),
        );
        Display:: display_header(get_lang('SearchResults'));
    } else {
        $interbreadcrumb[] = array(
            'url' => Security::remove_XSS(
                    $_SESSION['gradebook_dest']
                ).'?selectcat=1',
            'name' => get_lang('Gradebook'),
        );
        $interbreadcrumb[] = array(
            'url' => '#',
            'name' => get_lang('EditAllWeights'),
        );
        Display:: display_header('');
    }
}

?>
    <div class="actions">
        <a href="<?php echo Security::remove_XSS(
                $_SESSION['gradebook_dest']
            ).'?'.$my_api_cidreq ?>&selectcat=<?php echo $my_selectcat ?>">
            <?php echo Display::return_icon(
                'back.png',
                get_lang('FolderView'),
                '',
                ICON_SIZE_MEDIUM
            ); ?>
        </a>
    </div>
<?php

$form->display();

$formNormal = new FormValidator('normal_weight', 'post', $currentUrl);
$formNormal->addHeader(get_lang('EditWeight'));
$formNormal->display();

$warning_message = sprintf(get_lang('TotalWeightMustBeX'), $original_total);
Display::display_warning_message($warning_message, false);

?>
    <form method="post"
          action="gradebook_edit_all.php?<?php echo $my_api_cidreq ?>&selectcat=<?php echo $my_selectcat ?>">
        <table class="data_table">
            <tr class="row_odd">
                <th style="width: 35px;"><?php echo get_lang('Type'); ?></th>
                <th><?php echo get_lang('Resource'); ?></th>
                <th><?php echo get_lang('Weight'); ?></th>
            </tr>
            <?php echo $output; ?>
        </table>
        <input type="hidden" name="submitted" value="1"/>
        <br/>
        <button class="btn btn-primary" type="submit" name="name"
                value="<?php echo get_lang('Save') ?>">
            <?php echo get_lang('SaveScoringRules') ?>
        </button>
    </form>
<?php
Display:: display_footer();
