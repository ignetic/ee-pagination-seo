<?=form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=pagination_seo');?>

<h3><?= lang('pagination_seo_description'); ?></h3>

<?php

$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('available_tags'), 'style' => 'width:50%;'),
    lang('description')
);

$this->table->add_row('{pagination_seo:prev} <br> {pagination_seo:next}', lang('pagination_seo_rel_links_tag').':<br><code>&lt;link rel="prev" href="http://www.example.com/articles/P10" /&gt; <br> &lt;link rel="next" href="http://www.example.com/articles/P30" /&gt;</code>');
$this->table->add_row('{pagination_seo:prev_url} <br> {pagination_seo:next_url}', lang('pagination_seo_urls_tag').': <br> <code>http://www.example.com/articles/P10 <br> http://www.example.com/articles/P30</code> ');
$this->table->add_row('{pagination_seo:prev_uri} <br> {pagination_seo:next_uri}', lang('pagination_seo_uris_tag').': <br> <code>articles/P10 <br> articles/P30</code> ');
$this->table->add_row('{pagination_seo:title}', lang('pagination_seo_title_tag').':<br><code>&lt;title&gt;{title}{pagination_seo:title}&lt;/title&gt;</code>');
$this->table->add_row('{pagination_seo:description}', lang('pagination_seo_description_tag').'<br><code>&lt;meta name="description" content="{pagination_seo:description}{description}" /&gt;</code>');
$this->table->add_row('{pagination_seo:page_num} <br> {pagination_seo:total_pages} <br> {pagination_seo:total_items} ', lang('pagination_seo_vars_tag'));

echo $this->table->generate();
$this->table->clear();



$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('preference'), 'style' => 'width:50%;'),
    lang('setting')
);

foreach ($settings as $key => $val)
{
    $this->table->add_row(lang($key, $key), $val);
}

echo $this->table->generate();

?>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
<?php $this->table->clear()?>
<?=form_close()?>
<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/pagination_seo/views/index.php */