<?php
if (!defined('ABSPATH')) exit;

class MegaMenu_Widget extends \Elementor\Widget_Base {

  public function get_name() { return 'megamenu'; }
  public function get_title() { return 'Mega Menu'; }
  public function get_icon() { return 'eicon-nav-menu'; }
  public function get_categories() { return ['general']; }

  public function __construct($data = [], $args = null) {
    parent::__construct($data, $args);
    add_action('wp_enqueue_scripts', [$this, 'register_assets']);
  }

  public function register_assets() {
    // CSS
    wp_register_style('megamenu-style', false);
    wp_add_inline_style('megamenu-style', '
      .mega-menu{display:flex;flex-wrap:wrap;row-gap:var(--gap-y,0);column-gap:var(--gap-x,0);}
      .mega-menu__column{display:flex;flex-direction:column;gap:var(--col-gap,0);}
      .mega-menu__list{display:flex;flex-direction:column;list-style:none;margin:0;padding:0;gap:var(--ul-gap,0);}
      .mega-menu__list .mega-menu__list{margin-left:var(--indent,0);}
      .mega-menu__title{font-weight:700;display:block;transition:.25s;margin-bottom:var(--title-mb,0);}
      .mega-menu__title--toggle{cursor:pointer;}
      .mega-menu__link{display:block;transition:.25s;}
      @media(max-width:768px){.mega-menu__list{display:none}.mega-menu__column--open>.mega-menu__list{display:flex}}
    ');
    wp_enqueue_style('megamenu-style');

    // JS
    wp_register_script('megamenu-script', false, [], false, true);
    wp_add_inline_script('megamenu-script', "
      document.addEventListener('click',e=>{
        const t=e.target.closest('.mega-menu__title--toggle');
        if(!t) return;
        e.preventDefault();
        const c=t.closest('.mega-menu__column');
        const o=c.classList.toggle('mega-menu__column--open');
        t.setAttribute('aria-expanded',o?'true':'false');
      });
    ");
    wp_enqueue_script('megamenu-script');
  }

  protected function register_controls() {
    /* === Загальні === */
    $this->start_controls_section('mm_general', ['label'=>'Загальні']);
    $menus = [];
    foreach (wp_get_nav_menus() as $m) $menus[$m->term_id] = $m->name;
    $this->add_control('menu', ['label'=>'Меню','type'=>\Elementor\Controls_Manager::SELECT,'options'=>$menus]);
    $this->add_control('depth', ['label'=>'Глибина','type'=>\Elementor\Controls_Manager::NUMBER,'default'=>2,'min'=>1,'max'=>5]);
    $this->end_controls_section();

    /* === Контейнер (.mega-menu) === */
    $this->start_controls_section('mm_container', ['label'=>'Контейнер (.mega-menu)','tab'=>\Elementor\Controls_Manager::TAB_STYLE]);

    $this->add_responsive_control('c_display', [
      'label'=>'Тип відображення','type'=>\Elementor\Controls_Manager::SELECT,
      'options'=>['block'=>'Block','flex'=>'Flex','inline-flex'=>'Inline Flex','grid'=>'Grid'],
      'default'=>'flex','selectors'=>['{{WRAPPER}} .mega-menu'=>'display: {{VALUE}};'],
    ]);

    $this->add_responsive_control('c_direction', [
      'label'=>'Напрямок (flex-direction)','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'row'=>['title'=>'row','icon'=>'eicon-justify-start-h'],
        'row-reverse'=>['title'=>'row-reverse','icon'=>'eicon-justify-end-h'],
        'column'=>['title'=>'column','icon'=>'eicon-justify-start-v'],
        'column-reverse'=>['title'=>'column-reverse','icon'=>'eicon-justify-end-v'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'flex-direction: {{VALUE}};'],
      'condition'=>['c_display'=>['flex','inline-flex']],
    ]);

    $this->add_responsive_control('c_justify', [
      'label'=>'Вирівняти вміст по ширині','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'flex-start'=>['title'=>'start','icon'=>'eicon-h-align-left'],
        'center'=>['title'=>'center','icon'=>'eicon-h-align-center'],
        'flex-end'=>['title'=>'end','icon'=>'eicon-h-align-right'],
        'space-between'=>['title'=>'between','icon'=>'eicon-h-align-stretch'],
        'space-around'=>['title'=>'around','icon'=>'eicon-h-align-space-around'],
        'space-evenly'=>['title'=>'evenly','icon'=>'eicon-h-align-space-between'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'justify-content: {{VALUE}};'],
      'condition'=>['c_display'=>['flex','inline-flex']],
    ]);

    $this->add_responsive_control('c_align', [
      'label'=>'Вирівняти елементи по висоті','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'flex-start'=>['title'=>'top','icon'=>'eicon-v-align-top'],
        'center'=>['title'=>'center','icon'=>'eicon-v-align-middle'],
        'flex-end'=>['title'=>'bottom','icon'=>'eicon-v-align-bottom'],
        'stretch'=>['title'=>'stretch','icon'=>'eicon-v-align-stretch'],
        'baseline'=>['title'=>'baseline','icon'=>'eicon-editor-list-ol'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'align-items: {{VALUE}};'],
      'condition'=>['c_display'=>['flex','inline-flex']],
    ]);

    // Прогалини як у core: 4 поля. TOP = row-gap, RIGHT = column-gap
    $this->add_responsive_control('c_gap', [
      'label'=>'Прогалини','type'=>\Elementor\Controls_Manager::DIMENSIONS,
      'size_units'=>['px','em','rem','%'],
      'selectors'=>[
        '{{WRAPPER}} .mega-menu'=>'row-gap: {{TOP}}{{UNIT}}; column-gap: {{RIGHT}}{{UNIT}};',
      ],
    ]);

    $this->add_responsive_control('c_wrap', [
      'label'=>'Перенос (flex-wrap)','type'=>\Elementor\Controls_Manager::SELECT,
      'options'=>['nowrap'=>'nowrap','wrap'=>'wrap','wrap-reverse'=>'wrap-reverse'],
      'default'=>'wrap','selectors'=>['{{WRAPPER}} .mega-menu'=>'flex-wrap: {{VALUE}};'],
      'condition'=>['c_display'=>['flex','inline-flex']],
    ]);

    // width/height/max/min
    $this->add_responsive_control('c_w', ['label'=>'width','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'width: {{SIZE}}{{UNIT}};']]);
    $this->add_responsive_control('c_h', ['label'=>'height','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'height: {{SIZE}}{{UNIT}};']]);
    $this->add_responsive_control('c_min_w', ['label'=>'min-width','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'min-width: {{SIZE}}{{UNIT}};']]);
    $this->add_responsive_control('c_min_h', ['label'=>'min-height','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'min-height: {{SIZE}}{{UNIT}};']]);
    $this->add_responsive_control('c_max_w', ['label'=>'max-width','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'max-width: {{SIZE}}{{UNIT}};']]);
    $this->add_responsive_control('c_max_h', ['label'=>'max-height','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'max-height: {{SIZE}}{{UNIT}};']]);

    $this->end_controls_section();

    /* === Колонки (.mega-menu__column) === */
    $this->start_controls_section('mm_cols', ['label'=>'Колонки (.mega-menu__column)','tab'=>\Elementor\Controls_Manager::TAB_STYLE]);

    $this->add_responsive_control('col_display', [
      'label'=>'display','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'flex',
      'options'=>['block'=>'Block','flex'=>'Flex','inline-flex'=>'Inline Flex'],
      'selectors'=>['{{WRAPPER}} .mega-menu__column'=>'display: {{VALUE}};'],
    ]);

    $this->add_responsive_control('col_direction', [
      'label'=>'flex-direction','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'column'=>['title'=>'column','icon'=>'eicon-justify-start-v'],
        'row'=>['title'=>'row','icon'=>'eicon-justify-start-h'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu__column'=>'flex-direction: {{VALUE}};'],
      'condition'=>['col_display'=>['flex','inline-flex']],
    ]);

    $this->add_responsive_control('col_justify', [
      'label'=>'justify-content','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'flex-start'=>['title'=>'start','icon'=>'eicon-h-align-left'],
        'center'=>['title'=>'center','icon'=>'eicon-h-align-center'],
        'flex-end'=>['title'=>'end','icon'=>'eicon-h-align-right'],
        'space-between'=>['title'=>'between','icon'=>'eicon-h-align-stretch'],
        'space-around'=>['title'=>'around','icon'=>'eicon-h-align-space-around'],
        'space-evenly'=>['title'=>'evenly','icon'=>'eicon-h-align-space-between'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu__column'=>'justify-content: {{VALUE}};'],
      'condition'=>['col_display'=>['flex','inline-flex']],
    ]);

    $this->add_responsive_control('col_align', [
      'label'=>'align-items','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'flex-start'=>['title'=>'top','icon'=>'eicon-v-align-top'],
        'center'=>['title'=>'center','icon'=>'eicon-v-align-middle'],
        'flex-end'=>['title'=>'bottom','icon'=>'eicon-v-align-bottom'],
        'stretch'=>['title'=>'stretch','icon'=>'eicon-v-align-stretch'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu__column'=>'align-items: {{VALUE}};'],
      'condition'=>['col_display'=>['flex','inline-flex']],
    ]);

    // gap для колонки (звичайний один)
    $this->add_responsive_control('col_gap', [
      'label'=>'gap','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','em','rem'],
      'selectors'=>[
        '{{WRAPPER}} .mega-menu__column'=>'gap: {{SIZE}}{{UNIT}};',
        '{{WRAPPER}} .mega-menu'=>'--col-gap: {{SIZE}}{{UNIT}};',
      ],
    ]);

    // width/height/max/min
    foreach (['w'=>'width','h'=>'height','min_w'=>'min-width','min_h'=>'min-height','max_w'=>'max-width','max_h'=>'max-height'] as $key=>$prop){
      $this->add_responsive_control('col_'.$key, [
        'label'=>$prop,'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
        'selectors'=>['{{WRAPPER}} .mega-menu__column'=> $prop.': {{SIZE}}{{UNIT}};'],
      ]);
    }

    $this->add_group_control(\Elementor\Group_Control_Background::get_type(), ['name'=>'col_bg','selector'=>'{{WRAPPER}} .mega-menu__column']);
    $this->add_control('col_radius', ['label'=>'border-radius','type'=>\Elementor\Controls_Manager::SLIDER,'range'=>['px'=>['min'=>0,'max'=>50]],
      'selectors'=>['{{WRAPPER}} .mega-menu__column'=>'border-radius: {{SIZE}}{{UNIT}};']]);
    $this->end_controls_section();

    /* === Списки (.mega-menu__list) === */
    $this->start_controls_section('mm_ul', ['label'=>'Списки (.mega-menu__list)','tab'=>\Elementor\Controls_Manager::TAB_STYLE]);

    $this->add_responsive_control('ul_display', [
      'label'=>'display','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'flex',
      'options'=>['block'=>'Block','flex'=>'Flex','inline-flex'=>'Inline Flex'],
      'selectors'=>['{{WRAPPER}} .mega-menu__list'=>'display: {{VALUE}};'],
    ]);

    $this->add_responsive_control('ul_direction', [
      'label'=>'flex-direction','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'column'=>['title'=>'column','icon'=>'eicon-justify-start-v'],
        'row'=>['title'=>'row','icon'=>'eicon-justify-start-h'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu__list'=>'flex-direction: {{VALUE}};'],
      'condition'=>['ul_display'=>['flex','inline-flex']],
    ]);

    $this->add_responsive_control('ul_justify', [
      'label'=>'justify-content','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'flex-start'=>['title'=>'start','icon'=>'eicon-h-align-left'],
        'center'=>['title'=>'center','icon'=>'eicon-h-align-center'],
        'flex-end'=>['title'=>'end','icon'=>'eicon-h-align-right'],
        'space-between'=>['title'=>'between','icon'=>'eicon-h-align-stretch'],
        'space-around'=>['title'=>'around','icon'=>'eicon-h-align-space-around'],
        'space-evenly'=>['title'=>'evenly','icon'=>'eicon-h-align-space-between'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu__list'=>'justify-content: {{VALUE}};'],
      'condition'=>['ul_display'=>['flex','inline-flex']],
    ]);

    $this->add_responsive_control('ul_align', [
      'label'=>'align-items','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'flex-start'=>['title'=>'top','icon'=>'eicon-v-align-top'],
        'center'=>['title'=>'center','icon'=>'eicon-v-align-middle'],
        'flex-end'=>['title'=>'bottom','icon'=>'eicon-v-align-bottom'],
        'stretch'=>['title'=>'stretch','icon'=>'eicon-v-align-stretch'],
        'baseline'=>['title'=>'baseline','icon'=>'eicon-editor-list-ol'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu__list'=>'align-items: {{VALUE}};'],
      'condition'=>['ul_display'=>['flex','inline-flex']],
    ]);

    // gap для UL (одне поле)
    $this->add_responsive_control('ul_gap', [
      'label'=>'gap','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','em','rem'],
      'selectors'=>[
        '{{WRAPPER}} .mega-menu__list'=>'gap: {{SIZE}}{{UNIT}};',
        '{{WRAPPER}} .mega-menu'=>'--ul-gap: {{SIZE}}{{UNIT}};',
      ],
    ]);

    // width/height/max/min
    foreach (['w'=>'width','h'=>'height','min_w'=>'min-width','min_h'=>'min-height','max_w'=>'max-width','max_h'=>'max-height'] as $key=>$prop){
      $this->add_responsive_control('ul_'.$key, [
        'label'=>$prop,'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
        'selectors'=>['{{WRAPPER}} .mega-menu__list'=> $prop.': {{SIZE}}{{UNIT}};'],
      ]);
    }

    $this->add_responsive_control('ul_indent', [
      'label'=>'Відступ підпунктів','type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','em','rem','%'],
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'--indent: {{SIZE}}{{UNIT}};'],
    ]);
    $this->end_controls_section();

    /* === Пункти (.mega-menu__item) === */
    $this->start_controls_section('mm_li', ['label'=>'Пункти (.mega-menu__item)','tab'=>\Elementor\Controls_Manager::TAB_STYLE]);

    $this->add_responsive_control('li_display', [
      'label'=>'display','type'=>\Elementor\Controls_Manager::SELECT,'default'=>'flex',
      'options'=>['block'=>'Block','flex'=>'Flex','inline-flex'=>'Inline Flex'],
      'selectors'=>['{{WRAPPER}} .mega-menu__item'=>'display: {{VALUE}};'],
    ]);

    $this->add_responsive_control('li_justify', [
      'label'=>'justify-content','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'flex-start'=>['title'=>'start','icon'=>'eicon-h-align-left'],
        'center'=>['title'=>'center','icon'=>'eicon-h-align-center'],
        'flex-end'=>['title'=>'end','icon'=>'eicon-h-align-right'],
        'space-between'=>['title'=>'between','icon'=>'eicon-h-align-stretch'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu__item'=>'justify-content: {{VALUE}};'],
      'condition'=>['li_display'=>['flex','inline-flex']],
    ]);

    $this->add_responsive_control('li_align', [
      'label'=>'align-items','type'=>\Elementor\Controls_Manager::CHOOSE,'label_block'=>true,
      'options'=>[
        'flex-start'=>['title'=>'top','icon'=>'eicon-v-align-top'],
        'center'=>['title'=>'center','icon'=>'eicon-v-align-middle'],
        'flex-end'=>['title'=>'bottom','icon'=>'eicon-v-align-bottom'],
        'stretch'=>['title'=>'stretch','icon'=>'eicon-v-align-stretch'],
      ],
      'selectors'=>['{{WRAPPER}} .mega-menu__item'=>'align-items: {{VALUE}};'],
      'condition'=>['li_display'=>['flex','inline-flex']],
    ]);

    // width/height/max/min
    foreach (['w'=>'width','h'=>'height','min_w'=>'min-width','min_h'=>'min-height','max_w'=>'max-width','max_h'=>'max-height'] as $key=>$prop){
      $this->add_responsive_control('li_'.$key, [
        'label'=>$prop,'type'=>\Elementor\Controls_Manager::SLIDER,'size_units'=>['px','%','em','rem'],
        'selectors'=>['{{WRAPPER}} .mega-menu__item'=> $prop.': {{SIZE}}{{UNIT}};'],
      ]);
    }

    $this->add_responsive_control('link_pad', [
      'label'=>'Відступи в посиланні','type'=>\Elementor\Controls_Manager::DIMENSIONS,'size_units'=>['px','%','em','rem'],
      'selectors'=>['{{WRAPPER}} .mega-menu__link'=>'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
    ]);

    $this->add_group_control(\Elementor\Group_Control_Border::get_type(), ['name'=>'li_border','selector'=>'{{WRAPPER}} .mega-menu__item']);
    $this->add_responsive_control('li_radius', ['label'=>'border-radius','type'=>\Elementor\Controls_Manager::SLIDER,'range'=>['px'=>['min'=>0,'max'=>100]],
      'selectors'=>['{{WRAPPER}} .mega-menu__item'=>'border-radius: {{SIZE}}{{UNIT}};']]);

    $this->end_controls_section();

    /* === Типографія === */
    $this->start_controls_section('mm_typo', ['label'=>'Типографія']);
    $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
      'name'=>'typo_title','label'=>'Заголовки (.mega-menu__title)','selector'=>'{{WRAPPER}} .mega-menu__title'
    ]);
    $this->add_responsive_control('title_mb', [
      'label'=>'Відступ під заголовком','type'=>\Elementor\Controls_Manager::SLIDER,
      'selectors'=>['{{WRAPPER}} .mega-menu'=>'--title-mb: {{SIZE}}{{UNIT}};'],
    ]);
    $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
      'name'=>'typo_link','label'=>'Посилання (.mega-menu__link)','selector'=>'{{WRAPPER}} .mega-menu__link'
    ]);
    $this->end_controls_section();

    /* === Кольори === */
    $this->start_controls_section('mm_colors_title', ['label'=>'Кольори — Заголовки (.mega-menu__title)','tab'=>\Elementor\Controls_Manager::TAB_STYLE]);
    $this->start_controls_tabs('title_tabs');
      $this->start_controls_tab('title_normal',['label'=>'Normal']);
        $this->add_control('title_color', ['label'=>'Текст','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__title'=>'color: {{VALUE}};']]);
        $this->add_control('title_bg',    ['label'=>'Фон','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__title'=>'background-color: {{VALUE}};']]);
      $this->end_controls_tab();
      $this->start_controls_tab('title_hover',['label'=>'Hover']);
        $this->add_control('title_color_h',['label'=>'Текст','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__title:hover'=>'color: {{VALUE}};']]);
        $this->add_control('title_bg_h',   ['label'=>'Фон','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__title:hover'=>'background-color: {{VALUE}};']]);
      $this->end_controls_tab();
      $this->start_controls_tab('title_active',['label'=>'Active']);
        $this->add_control('title_color_a',['label'=>'Текст','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__column--open > .mega-menu__title'=>'color: {{VALUE}};']]);
        $this->add_control('title_bg_a',   ['label'=>'Фон','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__column--open > .mega-menu__title'=>'background-color: {{VALUE}};']]);
      $this->end_controls_tab();
    $this->end_controls_tabs();
    $this->end_controls_section();

    $this->start_controls_section('mm_colors_link', ['label'=>'Кольори — Посилання (.mega-menu__link)','tab'=>\Elementor\Controls_Manager::TAB_STYLE]);
    $this->start_controls_tabs('link_tabs');
      $this->start_controls_tab('link_normal',['label'=>'Normal']);
        $this->add_control('link_color', ['label'=>'Текст','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__link'=>'color: {{VALUE}};']]);
        $this->add_control('link_bg',    ['label'=>'Фон','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__link'=>'background-color: {{VALUE}};']]);
      $this->end_controls_tab();
      $this->start_controls_tab('link_hover',['label'=>'Hover']);
        $this->add_control('link_color_h',['label'=>'Текст','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__link:hover'=>'color: {{VALUE}};']]);
        $this->add_control('link_bg_h',   ['label'=>'Фон','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__link:hover'=>'background-color: {{VALUE}};']]);
      $this->end_controls_tab();
      $this->start_controls_tab('link_active',['label'=>'Active']);
        $this->add_control('link_color_a',['label'=>'Текст','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__item.current-menu-item > .mega-menu__link'=>'color: {{VALUE}};']]);
        $this->add_control('link_bg_a',   ['label'=>'Фон','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>['{{WRAPPER}} .mega-menu__item.current-menu-item > .mega-menu__link'=>'background-color: {{VALUE}};']]);
      $this->end_controls_tab();
    $this->end_controls_tabs();
    $this->end_controls_section();
  }

  protected function render() {
    $s = $this->get_settings_for_display();
    $menu = (int)($s['menu'] ?? 0);
    if (!$menu) return;
    $depth = max(1, (int)($s['depth'] ?? 2));

    $items = wp_get_nav_menu_items($menu);
    if (!$items) return;

    usort($items, fn($a,$b)=>$a->menu_order<=>$b->menu_order);
    $tree = [];
    foreach ($items as $it) $tree[(int)$it->menu_item_parent][] = $it;

    echo '<div class="mega-menu" role="navigation" aria-label="Mega Menu">';
    foreach ($tree[0] ?? [] as $col) {
      $has = !empty($tree[$col->ID]);
      echo '<div class="mega-menu__column'.($has?' mega-menu__column--has-children':'').'">';
      echo '<a href="'.esc_url($col->url?:'#').'" class="mega-menu__title'.($has?' mega-menu__title--toggle':'').'"'.($has?' aria-expanded="false"':'').'>'.esc_html($col->title).'</a>';
      if ($has && $depth>1) $this->render_sub($tree,$col->ID,$depth-1);
      echo '</div>';
    }
    echo '</div>';
  }

  private function render_sub($tree,$parent,$depth){
    if ($depth<=0 || empty($tree[$parent])) return;
    echo '<ul class="mega-menu__list">';
    foreach ($tree[$parent] as $ch){
      echo '<li class="mega-menu__item"><a href="'.esc_url($ch->url?:'#').'" class="mega-menu__link">'.esc_html($ch->title).'</a>';
      $this->render_sub($tree,$ch->ID,$depth-1);
      echo '</li>';
    }
    echo '</ul>';
  }
}
