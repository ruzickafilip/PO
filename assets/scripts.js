$("#btn-login").click(function(){
    Login();
});

$("#btn-signup").click(function(){
    SignUp();
});

$("#btn-logout").click(function(){
    Logout();
});

$("#btn-createSubject").click(function(){
    createSubject();
});

$("#btn-createGroup").click(function(){
    createGroup();
});

$("#btn-createEmployee").click(function(){
    createEmployee();
});

$(document).on("click",".subject-label",function() {
    deleteSubjectGroupRelation($(this).attr("data-idGroup"), $(this).attr("data-idSubject"), "subject-box" + ($(this).attr("data-idGroup")));
});

$('.assign-subjects-box').change(function(){ 
    var idSubject = $(this).val();
    var idGroup = $(this).attr("data-idGroup");
    var idElement = $(this).attr("id");
    addSubjectGroupRelation(idGroup, idSubject, idElement)
});




function SignUp() {

    $.ajax({
        type: "POST",
        dataType: "json",                                                                                                                  
        data: { "email" : $("#email-input").val(), "pwd" : $("#pwd-input").val(), "pwdconfirm" : $("#pwdconfirm-input").val(), "role" : $('#role-input').children(":selected").attr("id") },
        url: "http://po.utb/signup",
        success: function(data){
            if (data.result == 0) {
                $("#signup-alert").attr("style", "display: inline-block");
                $("#signup-alert").removeClass();
                $("#signup-alert").addClass("alert");
                $("#signup-alert").addClass("alert-danger");
                $("#signup-alert").text("Hesla se neshodují.");
                $("#signup-alert").attr("style", "width:100%");
            }
            if (data.result == 1) {
                $("#signup-alert").attr("style", "display: inline-block");
                $("#signup-alert").removeClass();
                $("#signup-alert").addClass("alert");
                $("#signup-alert").addClass("alert-success");
                $("#signup-alert").text("Uživatel zaregistrován.");
                $("#signup-alert").attr("style", "width:100%");
            }
        }
    });
    
}

function Login() {

    $.ajax({
        type: "POST",
        dataType: "json",
        data: { "email" : $("#email-login-input").val(), "pwd" : $("#pwd-login-input").val() },
        url: "http://po.utb/login",
        success: function(data){
            if (data.result == 0) {
                $("#login-alert").attr("style", "display: inline-block");
                $("#login-alert").removeClass();
                $("#login-alert").addClass("alert");
                $("#login-alert").addClass("alert-danger");
                $("#login-alert").text("Špatné jméno nebo heslo.");
                $("#login-alert").attr("style", "width:100%");
            }
            if (data.result == 1) {
                window.open(window.location.href,"_self")
            }
        }
    });
    
}

function Logout() {

    $.ajax({
        type: "POST",
        dataType: "json",
        url: "http://po.utb/logout",
        success: function(data){
            if (data.result == 1) {
                window.open(window.location.href,"_self")
            }
        }
    });
    
}



function createSubject() {

    $.ajax({
        type: "POST",
        dataType: "json",                                                                                                                  
        data: { 
        "shortcut" : $("#subject-shortcut-input").val(), 
        "weekCount" : $("#subject-weekCount-input").val(), 
        "lectureCount" : $("#subject-lectureCount-input").val(), 
        "exerciseCount" : $("#subject-exerciseCount-input").val(), 
        "seminarCount" : $("#subject-seminarCount-input").val(), 
        "endType" : $('#subject-endType-input').children(":selected").attr("id"),
        "language" : $('#subject-language-input').children(":selected").attr("id"),
        "classCount" : $("#subject-classCount-input").val(), 
        },
        url: "http://po.utb/create-subject",
        success: function(data){
            if (data.result == 0) {
                $("#createSubject-alert").attr("style", "display: inline-block");
                $("#createSubject-alert").removeClass();
                $("#createSubject-alert").addClass("alert");
                $("#createSubject-alert").addClass("alert-danger");
                $("#createSubject-alert").text("Předmět se nepodařilo vytvořit.");
                $("#createSubject-alert").attr("style", "width:100%");
            }
            if (data.result == 1) {
                $("#createSubject-alert").attr("style", "display: inline-block");
                $("#createSubject-alert").removeClass();
                $("#createSubject-alert").addClass("alert");
                $("#createSubject-alert").addClass("alert-success");
                $("#createSubject-alert").text("Předmět " + $("#subject-shortcut-input").val()+ " vytvořen.");
                $("#createSubject-alert").attr("style", "width:100%");
            }
        }
    });
    
}

function createGroup() {

    $.ajax({
        type: "POST",
        dataType: "json",                                                                                                                  
        data: { 
        "shortcut" : $("#group-shortcut-input").val(), 
        "grade" : $("#group-grade-input").val(), 
        "semester" : $("#group-semester-input").val(), 
        "studentCount" : $("#group-studentCount-input").val(), 
        "studyForm" : $("#group-studyForm-input").children(":selected").attr("id"), 
        "studyType" : $('#group-studyType-input').children(":selected").attr("id"),
        "language" : $("#group-language-input").val()
        },
        url: "http://po.utb/create-group",
        success: function(data){
            if (data.result == 0) {
                $("#createGroup-alert").attr("style", "display: inline-block");
                $("#createGroup-alert").removeClass();
                $("#createGroup-alert").addClass("alert");
                $("#createGroup-alert").addClass("alert-danger");
                $("#createGroup-alert").text("Skupinu se nepodařilo vytvořit.");
                $("#createGroup-alert").attr("style", "width:100%");
            }
            if (data.result == 1) {
                $("#createGroup-alert").attr("style", "display: inline-block");
                $("#createGroup-alert").removeClass();
                $("#createGroup-alert").addClass("alert");
                $("#createGroup-alert").addClass("alert-success");
                $("#createGroup-alert").text("Skupina " + $("#group-shortcut-input").val()+ " vytvořen.");
                $("#createGroup-alert").attr("style", "width:100%");
            }
        }
    });
    
}

function createEmployee() {

    doctor = $("#employee-doctor-input").children(":selected").attr("id");
    if (doctor = 'ano') {
        doctorResult = 1;
    } else {
        doctorResult = 0;
    }

    $.ajax({
        type: "POST",
        dataType: "json",                                                                                                                  
        data: { 
        "surname" : $("#employee-surname-input").val(), 
        "lastname" : $("#employee-lastname-input").val(), 
        "privatemail" : $("#employee-privatemail-input").val(), 
        "publicmail" : $("#employee-publicmail-input").val(), 
        "worktype" : $("#employee-worktype-input").val(),
        "doctor" : doctorResult,
        },
        url: "http://po.utb/create-employee",
        success: function(data){
            if (data.result == 0) {
                $("#createGroup-alert").attr("style", "display: inline-block");
                $("#createGroup-alert").removeClass();
                $("#createGroup-alert").addClass("alert");
                $("#createGroup-alert").addClass("alert-danger");
                $("#createGroup-alert").text("Zamestnance se nepodařilo vytvořit.");
                $("#createGroup-alert").attr("style", "width:100%");
            }
            if (data.result == 1) {
                $("#createGroup-alert").attr("style", "display: inline-block");
                $("#createGroup-alert").removeClass();
                $("#createGroup-alert").addClass("alert");
                $("#createGroup-alert").addClass("alert-success");
                $("#createGroup-alert").text("Zaměstnanec " + $("#group-shortcut-input").val()+ " vytvořen.");
                $("#createGroup-alert").attr("style", "width:100%");
            }
        }
    });
    
}

function deleteSubjectGroupRelation(idGroup, idSubject, idElement) {

    $.ajax({
        type: "POST",
        dataType: "json",                                                                                                                  
        data: { 
        "idGroup" : idGroup, 
        "idSubject" : idSubject
        },
        url: "http://po.utb/delete-subject-group-relation",
        success: function(data){

            $("#" + idElement).empty(); 

            var div2 = $("<option selected>  -  </option>");
            $("#" + idElement).prepend($(div2));

            data.result.unassignedSubjects.forEach(function(item) {
            $("#" + idElement).append($("<option></option>")
                .attr("value", item.idSubject).text(item.subjectName));
            });

        }
    });
    
}

function addSubjectGroupRelation(idGroup, idSubject, idElement) {

    $.ajax({
        type: "POST",
        dataType: "json",                                                                                                                  
        data: { 
        "idGroup" : idGroup, 
        "idSubject" : idSubject,
        "idElement" : idElement
        },
        url: "http://po.utb/add-subject-group-relation",
        success: function(data){

            var div=$("<div class='alert alert-warning alert-dismissible fade show' role='alert'><strong>" + data.result.subjectName + "</strong><button type='button' class='close subject-label' data-dismiss='alert' data-idSubject='" + idSubject + "' data-idGroup='" + idGroup + "' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div><div style='width:10px;'></div>");
            $("#groupRow" + idGroup).prepend(div);

            $("#" + idElement).empty(); 
            data.result.unassignedSubjects.forEach(function(item) {
            $("#" + idElement).append($("<option></option>")
                .attr("value", item.idSubject).text(item.subjectName));
            });

            var div2 = $("<option selected> - </option>");
            $("#" + idElement).prepend($(div2));

        }
    });
    
}

