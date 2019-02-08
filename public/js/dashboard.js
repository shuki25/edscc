
$(() => {
    var chartdata1;

    $.ajax({
        url: "{{ path('chart_daily_earning') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token('chart_dashboard') }}"
        },
        success: function (result) {
            if(result.status == 200) {
                console.log(result.data);
            }
            else {
                console.log('Problem with ajax call.')
            }
        },
        error: function (result) {
            alert('Internal Error.');
        }
    });


});
