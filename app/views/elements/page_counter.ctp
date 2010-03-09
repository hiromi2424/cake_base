<?php
echo $paginator->counter(array(
'format' => __('%count%件中%start% - %end%件目を表示しています', true)
));