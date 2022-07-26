import React from 'react';
import globals from 'src/globals';

export default class Clock extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            currentHours: '0:00',
            currentDate: '0<br> 0 0, 0000',
            serverHours: '0:00',
            serverDate: '0<br> 0 0, 0000',
            unitedTime: false,
        };
    }

    componentDidMount(){
        const serverTimeFormat = globals(['serverTimeFormat']);
        const serverDate = new Date (...serverTimeFormat.map( i => +i )) ;
        const currentDate = new Date ( );
        const _currentTime = currentDate.getTime();
        const _serverTime = serverDate.getTime();
        this.differentServerTime = _currentTime - _serverTime;
        if (this.differentServerTime < 300000) {
            this.setState({unitedTime: true});
        }

        this.timerID = setInterval(() => this.updateTime(), 1000)
    }

    componentWillUnmount(){
        clearInterval(this.timerID)
    }

    updateClock (currentTime, clockSelector )
    {
        let currentHours = currentTime.getHours ( );
        let currentMinutes = currentTime.getMinutes ( );
        let currentSeconds = currentTime.getSeconds ( );

        currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
        currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;

        const currentTimeString = currentHours + ":" + currentMinutes;
        this.setState({[clockSelector + "Hours"]: currentTimeString});

        const dayOfWeek = globals(['tr', 'dayOfWeek']);
        const currentDay = dayOfWeek[currentTime.getDay()];
        const currentDateW = currentTime.getDate();
        const numberMonth = currentTime.getMonth();
        const monthNames = globals(['tr', 'monthNames']);
        const currentMonth = monthNames[numberMonth];
        const currentYear = currentTime.getFullYear();
        const currentDateString = currentDay + "<br>" + currentDateW + " " + currentMonth + ", " + currentYear;
        this.setState({[clockSelector + "Date"]: currentDateString});
    }

    updateTime(){
        const currentTime = new Date ();
        const serverTime = new Date (currentTime.getTime() - this.differentServerTime);
        this.updateClock(currentTime, "current");
        this.updateClock(serverTime, "server")
    }

    render() {
        if (this.state.unitedTime) {
            return (
                <div className="time">
                    <div className="united-date" data-time="">
                        <div className="clock">
                            <i className="icon-clock-o"></i>
                            <span className="clock">{this.state.currentHours}</span>
                        </div>
                        <div className="date-holder">
                            <i className="icon-calendar-o"></i>
                            <span className="date" dangerouslySetInnerHTML={{__html: this.state.currentDate}}/>
                        </div>
                    </div>
                </div>
            );
        } else {
            return (
                <div className="time">
                    <div className="current-date">
                        <div className="text">{globals(['tr', 'TEXT_CURRENT_TIME'])}</div>
                        <div className="clock"><span className="clock">{this.state.currentHours}</span></div>
                        <div className="date-holder"><span className="date" dangerouslySetInnerHTML={{__html: this.state.currentDate}}/></div>
                    </div>
                    <div className="server-date">
                        <div className="text">{globals(['tr', 'TEXT_SERVER_TIME'])}</div>
                        <div className="clock"><span className="clock">{this.state.serverHours}</span></div>
                        <div className="date-holder"><span className="date" dangerouslySetInnerHTML={{__html: this.state.serverDate}}/></div>
                    </div>
                </div>
            );
        }
    }
}