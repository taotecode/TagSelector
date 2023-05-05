<?php

/**
 * 文章编辑页面标签选择器插件
 *
 * @package TagSelector
 * @author Yuanzhumc
 * @version 1.0.0
 * @link https://blog.berfen.com/76.html
 */
class TagSelector_Plugin implements Typecho_Plugin_Interface
{
  public static function activate()
  {
    Typecho_Plugin::factory('admin/write-post.php')->bottom = array('TagSelector_Plugin', 'render');
    Typecho_Plugin::factory('admin/write-page.php')->bottom = array('TagSelector_Plugin', 'render');
  }

  public static function deactivate()
  {
  }

  public static function config(Typecho_Widget_Helper_Form $form)
  {
  }

  public static function personalConfig(Typecho_Widget_Helper_Form $form)
  {
  }

  public static function render()
  {
    $db = Typecho_Db::get();
    $tags = $db->fetchAll($db->select()->from('table.metas')->where('type = ?', 'tag'));

    $tagsArray = [];
    foreach ($tags as $tag) {
      $tagsArray[] = $tag['name'];
    }
    $tagsJson = json_encode($tagsArray);

    echo <<<HTML
<style>
.tag-selector {
    margin: 10px 0;
    padding: 5px;
    border: 1px solid #ccc;
    max-height: 150px;
    overflow-y: auto;
    display: flex;
    flex-wrap: wrap;
}
.tag-selector span {
    background-color: #f3f3f3;
    padding: 3px 8px;
    margin: 2px;
    border-radius: 3px;
    cursor: pointer;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let tagsArray = {$tagsJson};
    let tagsInput = document.getElementById('tags');
    let tokenInput = document.getElementById('token-input-tags');

    function addTag(tag) {
        if (tokenInput) {
            tokenInput.value = tag;
            const keyboardEvent = new KeyboardEvent('keydown', {
                key: 'Enter',
                code: 'Enter',
                keyCode: 13,
                which: 13,
            });
            tokenInput.dispatchEvent(keyboardEvent);
            tagsArray = tagsArray.filter(t => t !== tag);
            renderTags();
        }
    }

    function renderTags() {
        let tagsHTML = '';

        tagsArray.forEach(tag => {
            tagsHTML += '<span data-tag="'+tag+'">'+tag+'</span>';
        });

        tagSelector.innerHTML = tagsHTML;

        tagSelector.querySelectorAll('span').forEach(span => {
            span.addEventListener('click', function () {
                addTag(this.dataset.tag);
            });
        });
    }

    const tagSelector = document.createElement('div');
    tagSelector.classList.add('tag-selector');

    const tagsElement = document.getElementById('advance-panel');
    if (tagsElement) {
        tagsElement.parentNode.insertBefore(tagSelector, tagsElement.nextSibling);
    }

    // 将编辑页面中已有的标签从标签选择框中删除
    if (tagsInput) {
        const inputTags = tagsInput.value.split(',').map(t => t.trim()).filter(t => t.length > 0);
        tagsArray = tagsArray.filter(tag => !inputTags.includes(tag));
    }

    renderTags();
});
</script>
HTML;
  }

}
