<div class="wrap">
    <h2><?php _e('Manage Editions', 'daily-edition') ?></h2>

    <div id="nav-menus-frame">
        <div id="menu-settings-column" class="metabox-holder">
            <div class="postbox">
                <h3><?php _e('Archives', 'daily-edition') ?></h3>

                <div class="inside">
                    <table class="widefat">
                        <tbody>
                        <tr>
                            <th><a href="?page=editions"><?php _e('New Edition', 'daily-edition') ?></a></th>
                        </tr>
                        <?php foreach ($editions as $edition): ?><tr class="<?php echo $edition->edition_number === $_GET['edition_id'] ? 'active' : '' ?>">
                            <th><a href="?page=editions&edition_id=<?php echo esc_attr($edition->edition_number) ?>" class="widelink">
                                    <?php printf(__('N°%d <small>(%s)</small>', 'daily-edition'), $edition->edition_number, mysql2date('j/m/Y', $edition->post_date)) ?>
                            </a></th>
                        </tr><?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="menu-management-liquid">
            <form method="post" action="edit.php?page=editions">
                <?php wp_nonce_field('daily-edition-new') ?>

                <div class="tablenav top">
                    <div class="alignleft actions">
                        <?php if ($edition_id): ?>
                        <h3><?php _e('Edition #', 'daily-edition') ?><?php echo $edition_number ?></h3>
                        <?php else: ?>
                        <label for="edition-number"><?php _e('Edition #', 'daily-edition') ?></label>
                        <input id="edition-number" name="edition_number" type="number" value="<?php echo esc_attr($edition_number) ?>" size="4" min="1" required>
                        <?php printf(__('for the <b>%s</b>', 'daily-edition'), $edition_date) ?>.
                        <?php endif ?>
                    </div>
                </div>

                <?php if (!empty($posts)): ?>
                <table class="wp-list-table widefat">
                    <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col"><?php _e('Unpublish?', 'daily-edition') ?></th>
                        <th scope="col"><?php _e('Title') ?></th>
                        <th scope="col"><?php _e('Categories') ?></th>
                        <th scope="col"><?php _e('Date') ?></th>
                    </tr>
                    </thead>
                    <tbody class="ui-sortable">
                        <?php foreach ($posts as $i => $post): ?>
                        <tr id="postId_<?php echo esc_attr($post->ID) ?>">
                            <td><span class="handle"></span> <input type="text" size="2" name="post_order[<?php echo esc_attr($post->ID) ?>]" value="<?php echo get_post_meta($post->ID, 'order', true) ? get_post_meta($post->ID, 'order', true) : $i + 1 ?>"></td>
                            <td><input type="checkbox" name="post_unpublish[<?php echo esc_attr($post->ID) ?>]" value="1"></td>
                            <td><?php echo $post->post_title ?></td>
                            <td><?php the_category(', ', false, $post->ID) ?></td>
                            <td><?php echo $post->post_date ?></td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>

                <div class="tablenav bottom">
                    <div class="alignleft actions">
                        <input class="button-primary action" type="submit" value="<?php esc_attr_e('Update this edition', 'daily-edition') ?>">
                    </div>
                </div>
                <?php endif ?>

                <h3><?php _e('Not Yet Published Posts', 'daily-edition') ?></h3>
                <table class="wp-list-table widefat">
                    <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col"><?php _e('Publish?', 'daily-edition') ?></th>
                        <th scope="col"><?php _e('Title') ?></th>
                        <th scope="col"><?php _e('Categories') ?></th>
                        <th scope="col"><?php _e('Date') ?></th>
                    </tr>
                    </thead>
                    <tbody class="ui-sortable">
                    <?php foreach ($unpublished as $i => $post): ?>
                    <tr id="postId_<?php echo esc_attr($post->ID) ?>">
                        <td><span class="handle"></span> <input type="text" size="2" name="post_order[<?php echo esc_attr($post->ID) ?>]" value="<?php echo get_post_meta($post->ID, 'order', true) ? get_post_meta($post->ID, 'order', true) : $i + 1 ?>"></td>
                        <td><input type="checkbox" name="post_publish[<?php echo esc_attr($post->ID) ?>]" value="1" checked="checked"></td>
                        <td><?php echo $post->post_title ?></td>
                        <td><?php the_category(', ', false, $post->ID) ?></td>
                        <td><?php echo $post->post_date ?></td>
                    </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>

                <div class="tablenav bottom">
                    <div class="alignleft actions">
                        <?php if ($edition_id): ?>
                        <input class="button-primary action" type="submit" value="<?php esc_attr_e('Add to this edition', 'daily-edition') ?>">
                        <?php else: ?>
                        <input class="button-primary action" type="submit" value="<?php esc_attr_e('Create this new edition, now', 'daily-edition') ?>">
                        <?php endif ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function($){
    $(function(){
        $('tbody.ui-sortable').sortable({
            "axis": "y",
            "cursor": "move",
            "handle": ".handle",
            "stop": function(event, ui){
                var $this = $(this);

                $.each($this.sortable('toArray'), function(counter, id){
                    $('#'+id).find('input[name^="post_order"]').val(counter+1);
                });
            }
        }).find('input[name^="post_order"]').hide();
    });
})(jQuery);
</script>

<style type="text/css" media="all">
.handle{
    background: transparent url('data:image/png;base64,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') no-repeat center center;
    display: block;
    height: 20px;
    width: 20px;
    cursor: hand;
    cursor: -moz-grab;
    cursor: -webkit-grab;
    cursor: grab;
}

.handle:active{
    cursor: hand;
    cursor: -moz-grabbing;
    cursor: -webkit-grabbing;
    cursor: grabbing;
}

.widelink{
    display: block;
}
    .active .widelink{
        font-weight: bold;
    }
</style>