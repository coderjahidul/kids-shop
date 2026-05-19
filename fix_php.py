for filename in ['header.php', 'footer.php', 'front-page.php']:
    with open(filename, 'r', encoding='utf-8') as f:
        content = f.read()
    
    content = content.replace('&lt;?php', '<?php').replace('?&gt;', '?>')
    
    with open(filename, 'w', encoding='utf-8') as f:
        f.write(content)
