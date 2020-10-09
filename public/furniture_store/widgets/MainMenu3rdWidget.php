<?php 
function build_menu($node_arr, $level = 0)
{
    $result = $level?'<ul class="rd-menu rd-navbar-dropdown">':'<ul class="rd-navbar-nav">';
    $i = 0;
    while ($i < count($node_arr))
    {
        if ($node_arr[$i]['children'] > 0) {
            $result .= $level == 1? '<li class="rd-dropdown-item rd-nav-item rd-nav-item rd-navbar--has-dropdown rd-navbar-submenu">' : '<li class="rd-nav-item rd-nav-item rd-navbar--has-dropdown rd-navbar-submenu">';
            $result .= $level == 1? '<a class="rd-dropdown-link" href="'.@$node_arr[$i]['data']['link'].'">'.$node_arr[$i]['data']['name'].'</a>' : '<a class="rd-nav-link" href="'.@$node_arr[$i]['data']['link'].'">'.$node_arr[$i]['data']['name'].'</a>';
            $new_node_arr = array_slice($node_arr, $i + 1, $node_arr[$i]['children']);
            $result.= build_menu($new_node_arr, $level + 1);
            $result .= '</li>';
            $i += $node_arr[$i]['children'] + 1;
        } else {
            $result.= $level? '<li class="rd-dropdown-item"><a class="rd-dropdown-link" href="'.@$node_arr[$i]['data']['link'].'">'.$node_arr[$i]['data']['name'].'</a></li>' : '<li class="rd-nav-item"><a class="rd-nav-link" href="'.@$node_arr[$i]['data']['link'].'">'.$node_arr[$i]['data']['name'].'</a></li>';
            $i++;
        }
    }
    $result.= "</ul>";
    
    return $result;
}
if(!empty($node_arr)) {
    echo build_menu($node_arr, 0);
}
?>
<?php if(!empty($node_arr)):?>
<?php if($this->isAdmin()):?>
 <a href="<?php echo $this->getBaseUrl();?>index.php?controller=AdminMenus&action=Update&id=<?php echo $menu_id?>" style="color: red;"> <i class="fa fa-pencil" aria-hidden="true"></i></a>
<?php endif;?>
<?php endif;?>