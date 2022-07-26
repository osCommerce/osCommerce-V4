<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD Xhtml 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output encoding="UTF-8" indent="no" method="html"  doctype-public="-//W3C//DTD Xhtml 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<xsl:template match="/">
 <table width="350" height="20" id="ss" class="suggestTable">
 <xsl:for-each select="response/suggestlist/item">
  <tr id="{trid}" onmouseover="myrow = this.getElementsByTagName('td').item(0);myrow1 = this.getElementsByTagName('td').item(1); myrow.className='suggestTableRowOver'; myrow1.className='suggestTableRowOverRight';" onmouseout="myrow = this.getElementsByTagName('td').item(0); myrow1 = this.getElementsByTagName('td').item(1); myrow.className='suggestTableRow'; myrow1.className='suggestTableRowRight';">
   <td id="{tdid}" onclick="window.location.href = '{url}';" class="suggestTableRow"><xsl:value-of select="name" disable-output-escaping="yes"/></td>
   <td id="{tdid}1" onclick="window.location.href = '{url}';" class="suggestTableRowRight" align="right"><xsl:value-of select="type" disable-output-escaping="yes"/></td>
  </tr>
 </xsl:for-each>
 </table>
</xsl:template>
</xsl:stylesheet>
