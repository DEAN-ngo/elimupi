<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="utf-8" indent="yes" />

	<xsl:template match='errors'>
		<errors>
			<xsl:apply-templates select='error'/>
		</errors>
	</xsl:template>

	<xsl:template match='error'>
		<error><xsl:value-of select='text()'/></error>
	</xsl:template>

	<xsl:variable name='url'>../Content/</xsl:variable>

	<xsl:variable name='lang'>en</xsl:variable>

	<xsl:variable name='search'></xsl:variable>

	<xsl:variable name='select'>reduced</xsl:variable>

	<xsl:template match="Packages">
		<items>
			<xsl:apply-templates>
				<xsl:sort select="ReleaseDate" order="descending" />
			</xsl:apply-templates>
		</items>
	</xsl:template>

	<xsl:template match="Package">
		<xsl:if test="contains( translate(Descriptions/Description, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), $search)">
			<xsl:if test="($select = 'all' or Type = 'Html' or Type = 'PDF') and Descriptions/Description[@xml:lang = $lang or @xml:lang = 'en']">
				<item>
					<xsl:apply-templates select="Descriptions" />
				</item>
			</xsl:if>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="Descriptions">
		<xsl:choose>
			<xsl:when test="Description[@xml:lang = $lang]">
				<ul>
					<xsl:attribute name='rel'><xsl:value-of select='../UniqueId'/></xsl:attribute>
					<xsl:apply-templates select="Description[@xml:lang = $lang]" />
				</ul>
			</xsl:when>
			<xsl:otherwise test="Description[@xml:lang = 'en']">
				<ul>
					<xsl:attribute name='rel'><xsl:value-of select='../UniqueId'/></xsl:attribute>
					<xsl:apply-templates select="Description[@xml:lang = 'en']" />
				</ul>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="UniqueId">
		<xsl:if test="$select='all' and ../Type = 'Apk'">
			<img src='img/sync.svg' class='installable pointer'>
				<xsl:attribute name='rel'>
					<xsl:value-of select='../UniqueId'/>
				</xsl:attribute>
				<xsl:attribute name='type'>
					<xsl:value-of select='../Type'/>
				</xsl:attribute>
			</img>
			<xsl:text> </xsl:text>
		</xsl:if>
		<xsl:if test="../Type = 'Html'">
			<xsl:apply-templates select="../HtmlIndexFiles" />
			<xsl:apply-templates select="../IndexHtmlCreateFromTemplate" />
			<xsl:text> </xsl:text>
		</xsl:if>
		<xsl:if test="../Accessibility = 'Public' and ../Type = 'Pdf'">
			<a class='downloadable'>
				<xsl:attribute name='rel'>
					<xsl:value-of select='text()' />
				</xsl:attribute>
			</a>
			<xsl:text> </xsl:text>
		</xsl:if>
	</xsl:template>

	<xsl:template match="IndexHtmlCreateFromTemplate">
		<xsl:apply-templates select="HtmlSourceDirectory" />
	</xsl:template>

	<xsl:template match="HtmlIndexFiles">
		<xsl:choose>
			<xsl:when test="HtmlIndexFile[@xml:lang = $lang]">
				<xsl:apply-templates select="HtmlIndexFile[@xml:lang = $lang]" />
			</xsl:when>
			<xsl:otherwise test="HtmlIndexFile[@xml:lang = '1en']">
				<xsl:apply-templates select="HtmlIndexFile[@xml:lang = 'en']" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="HtmlSourceDirectory">
		<xsl:if test="@xml:lang = $lang or @xml:lang = 'en'">
			<a class='viewable' target='_blank'>
				<xsl:attribute name='href'>
					<xsl:value-of select='$url' />
					<xsl:text>/</xsl:text>
					<xsl:value-of select='../../UniqueId'/>
					<xsl:text>/</xsl:text>
					<xsl:value-of select='text()'/>
				</xsl:attribute>
			</a>
		</xsl:if>
	</xsl:template>	

	<xsl:template match="HtmlIndexFile">
		<a class='viewable' target='_blank'>
			<xsl:attribute name='href'>
				<xsl:value-of select='$url' />
				<xsl:text>/</xsl:text>
				<xsl:value-of select='../../UniqueId'/>
				<xsl:text>/</xsl:text>
				<xsl:value-of select='text()'/>
			</xsl:attribute>
		</a>
	</xsl:template>

	<xsl:template match="Description">
		<li><xsl:value-of select="text()" /></li>
		<li>
			<xsl:apply-templates select="../../UniqueId" />
			<xsl:apply-templates select="../../ReleaseDate" />
		</li>
	</xsl:template>

	<xsl:template match="ReleaseDate">
		<date><xsl:value-of select="text()" /></date>
	</xsl:template>
	
</xsl:stylesheet>