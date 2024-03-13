const subjects = [
    {
        subjectName: 'Webtervezés',
        subjectCode: 'INF0001',
        credit: 2,
        type: 'Gyakorlat'
    },
    {
        subjectName: 'Pronya',
        subjectCode: 'INF0002',
        credit: 2,
        type: 'Előadás'
    }
]

const WTCourses = [
    {
        curseCode: 'INF0001-1',
        classroomCode: 'IR217-3',
        time: 'Kedd 16:00-17:00',
        teacher: 'Szobonya Dávid'
    },
    {
        curseCode: 'INF0001-2',
        classroomCode: 'IR217-3',
        time: 'Csütörtök 15:00-16:00',
        teacher: 'Kolláth István Tibor'
    }
]

// on load function:
$(() => {
    const contentElement = $('#content')

    const columns = {
        subjectName: 'Név',
        subjectCode: 'Kód',
        credit: 'Kredit',
        type: 'Típus'
    }

    const subjectTable = tableBuilder.createDropDownTable(columns, subjects, openSubject)
    contentElement.append(subjectTable)
})

function openSubject() {
    const columns = {
        curseCode: 'Kurzus kód',
        classroomCode: 'Tanterem',
        time: 'Időpont',
        teacher: 'Oktató'
    }

    return tableBuilder.createDropDownTable(columns, WTCourses, () => { return 'asd' })
}
