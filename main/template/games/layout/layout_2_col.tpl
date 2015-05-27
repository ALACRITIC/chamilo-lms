{% extends template ~ "/layout/main.tpl" %}

{% block body %}
	{# Main content #}

    {# Plugin main top #}
    {% if plugin_main_top %}
        <div id="plugin_main_top" class="col-md-12">
            {{ plugin_main_top }}
        </div>
    {% endif %}

        <!-- Inicia el slider -->
            {# Announcements  #}
            {% if announcements_block %}
                <div class="slider-top">
                    {{ announcements_block }}
                </div>
            {% endif %}
        <!-- fin del slider -->


	    {#  Right column #}
	    <div class="col-md-3 menu-column">
        {% if plugin_menu_top %}
            <div id="plugin_menu_top">
                {{plugin_menu_top}}
            </div>
        {% endif %}



		{# User picture #}

        {{ user_image_block }}

        {# User Profile links #}
		{{ profile_block }}


        {# Course block - admin #}
		{{ course_block }}

        {# Course block - teacher #}
		{{ teacher_block }}

        {# Skills #}
        {{ skills_block }}

        {# Certificates search block #}
        {{ certificates_search_block }}

		{# Notice #}
		{{ notice_block }}

        {# Help #}
		{{ help_block }}

		{# Links that are not added in the tabs #}
		{{ navigation_course_links }}

		{# Search (xapian) #}
		{{ search_block }}

		{# Classes #}
		{{ classes_block }}

		{# Plugin courses sidebar #}
        {# Plugins for footer section #}

        {% if plugin_menu_bottom %}
            <div id="plugin_menu_bottom">
                {{ plugin_menu_bottom }}
            </div>
        {% endif %}
	</div>

	<div class="col-md-9">
        {# Plugin bottom #}
        {% if plugin_content_top %}
            <div id="plugin_content_top">
                {{ plugin_content_top }}
            </div>
        {% endif %}

		{# Portal homepage #}
        {% if home_page_block %}
            <section id="homepage-home">
                {{ home_page_block }}
            </section>
        {% endif %}

		{#  ??  #}
		{{ sniff_notification }}

        {% block page_body %}
        {% include template ~ "/layout/page_body.tpl" %}
        {% endblock %}

        {# Welcome to course block  #}
        {% if welcome_to_course_block %}
            <section id="homepage-course">
            {{ welcome_to_course_block }}
            </section>
        {% endif %}

        {% block content %}
        {% if content is not null %}
            <section id="page-content">
                {{ content }}
            </section>
        {% endif %}
        {% endblock %}



        {# Course categories (must be turned on in the admin settings) #}
        {% if course_category_block %}
            <section id="homepage-course-category">
                {{ course_category_block }}
            </section>
        {% endif %}

		{# Hot courses template  #}
		{% include template ~ "/layout/hot_courses.tpl" %}

        {# Content bottom  #}
        {% if plugin_content_bottom %}
            <div id="plugin_content_bottom">
                {{plugin_content_bottom}}
            </div>
        {% endif %}
        &nbsp;
	</div>

    {# Plugin main bottom #}
    {% if plugin_main_bottom %}
        <div id="plugin_main_bottom" class="col-md-12">
            {{ plugin_main_bottom }}
        </div>
    {% endif %}

{% endblock %}
