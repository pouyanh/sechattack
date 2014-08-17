<!DOCTYPE html>
<html>
<head>
    <title>{% block title %}Main Template{% endblock %}</title>
</head>
<body>
    {% block content %}
        {{ content() }}
    {% endblock %}
</body>
</html>