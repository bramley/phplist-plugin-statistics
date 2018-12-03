<html>
<style>
/* Define page size. Requires print-area adjustment! */
body {
    margin:     0;
    padding:    0;
    width:      21cm;
}

/* Printable area */
#print-area {
    position:   relative;
    top:        0.5cm;
    left:       2cm;
    width:      18cm;

    font-size:  12px;
    font-family: Verdana,sans-serif;
}

.caption, .captionA {
    font-size:  12px;
    font-weight: bold;
}
.captionA {
    width: 180;
}
.value, .value-right {
    font-size:  12px;
    font-weight: normal;
    color: #666666;
}
.value-right {
    text-align: right;
    width: 60;
}
.sub-head {
    padding-top: 20px;
    font-size:  12px;
    font-weight: bold;
    border-bottom: dotted 1px #666666;
}

</style>
<body>
<div id='print-area'>
<hr>
<table width='100%'>
<tbody>
<tr>
<td width='15%' class='caption'>From</td><td width='40%' class='value'><?php echo $from; ?></td>
<td width='15%' class='caption'>Mailing List</td><td width='30%' class='value'><?php echo $list; ?></td>
</tr>
<?php if (isset($campaigntitle)): ?>
<tr>
<td width='15%' class='caption'>Title</td><td width='40%' class='value'><?php echo $campaigntitle; ?></td>
</tr>
<?php endif; ?>
<tr>
<td width='15%' class='caption'>Subject</td><td width='40%' class='value'><?php echo $subject; ?></td>
</tr>
<tr>
<td width='15%' class='caption'>Start Date</td><td width='40%' class='value'><?php echo $start; ?></td>
</tr>
<tr>
<td width='15%' class='caption'>End Date</td><td width='40%' class='value'><?php echo $end; ?></td>
</tr>
</tbody>
</table>
<table>
<tbody>
<tr><td colspan='3' class='sub-head'>Of the total sent</td></tr>
<tr><td class='captionA'>Sent</td><td class='value-right' ><?php echo $sent; ?></td><td class='value-right'></td></tr>
<tr><td class='captionA'>Delivered</td><td class='value-right' ><?php echo $delivered; ?></td><td class='value-right'><?php echo $deliveredrate; ?></td></tr>
<tr><td class='captionA'>Bounced</td><td class='value-right' ><?php echo $bounced; ?></td><td class='value-right'><?php echo $bouncerate; ?></td></tr>
<tr><td colspan='3' class='sub-head'>Of the total delivered</td></tr>
<tr><td class='captionA'>Opened</td><td class='value-right' ><?php echo $opened; ?></td><td class='value-right'><?php echo $openrate; ?></td></tr>
<tr><td class='captionA'>Not opened</td><td class='value-right' ><?php echo $unopened; ?></td><td class='value-right'><?php echo $unopenrate; ?></td></tr>
<tr><td colspan='3' class='sub-head'>Of the total who opened</td></tr>
<tr><td class='captionA'>Users who clicked</td><td class='value-right' ><?php echo $clicked; ?></td><td class='value-right'><?php echo $clickopenrate; ?></td></tr>
<tr><td class='captionA'>Total number of clicks</td><td class='value-right' ><?php echo $totalClicks; ?></td><td class='value-right'><?php ?></td></tr>
<tr><td class='captionA'>Users who forwarded</td><td class='value-right' ><?php echo $forwarded; ?></td><td class='value-right'><?php echo $forwardedrate; ?></td></tr>
</tbody>
</table>
</body>
</html>
