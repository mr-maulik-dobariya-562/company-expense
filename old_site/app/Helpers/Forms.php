<?php

namespace App\Helpers;


class Forms
{

    public static function select($name, $options, $old = '', $class = '', $forListItem = false)
    {

        ?>

        <select class="form-control <?php echo e($class) ?>" <?php if ($forListItem) echo '__name__';
           else echo 'name'; ?>="<?php echo e($name) ?>">

            <?php

            if (!empty($options)):

                foreach ( $options as $option ):
                    $selected = '';
                    if ($old == $option[ 'id' ]) $selected = 'selected' ?>

                        <option value="<?php echo e($option[ 'id' ]) ?>" <?php echo e($selected) ?>><?php echo e($option[ 'name' ]) ?></option>

                <?php endforeach; endif; ?>

        </select>

        <?php

    }



    public static function select2($name, $options, $old = [], $multiple = false)
    {

        ?>

        <select <?php if ($multiple) echo "multiple"; ?> <?php if(!isset($options[ 'id' ])){ echo ' id="select_'.$name.'"'; } ?> <?php if (isset($options[ 'required' ])) echo "required"; ?> class="form-control dungdt-select2-field <?= $options[ 'class' ] ?? '' ?>"
            data-options='<?php echo e(json_encode($options[ 'configs' ])) ?>' name="<?php echo e($name) ?>">

            <?php if ($multiple): ?>

                <?php foreach ( $old as $item ): ?>

                    <option data-value='<?php echo e(json_encode($item)) ?>' value="<?php echo e($item[ 'id' ]) ?>" selected>
                        <?php echo e($item[ 'text' ]) ?></option>

                <?php endforeach; ?>

            <?php else: ?>

                <?php if (!empty($old[ 1 ])): ?>

                    <option data-value='<?php echo json_encode($old[ 2 ] ?? []) ?>' value="<?php echo e($old[ 0 ]) ?>" selected>
                        <?php echo e($old[ 1 ]) ?></option>

                <?php endif; ?>

            <?php endif; ?>

        </select>

        <?php

    }
}
