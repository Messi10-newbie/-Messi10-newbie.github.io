import java.util.Objects;

public abstract class Seat {
    private final String  seatId;
    private boolean booked;
    private String  bookedTicketType;
    private double  bookedPrice;

    public Seat(String seatId) {
        this.seatId           = seatId;
        this.booked           = false;
        this.bookedTicketType = "";
        this.bookedPrice      = 0.0;
    }

    public String  getSeatId()             { return seatId; }
    public boolean isBooked()              { return booked; }
    public String  getBookedTicketType()   { return bookedTicketType; }
    public double  getBookedPrice()        { return bookedPrice; }

    public void book(String ticketType, double price) {
        this.booked           = true;
        this.bookedTicketType = ticketType;
        this.bookedPrice      = price;
    }

    public void cancel() {
        this.booked           = false;
        this.bookedTicketType = "";
        this.bookedPrice      = 0.0;
    }

    public abstract double getPrice();
    public abstract String getType();

    @Override
    public String toString() {
        return getType() + " Seat[id=" + seatId + ", booked=" + booked + ", price=RM" + getPrice() + "]";
    }

    @Override
    public int hashCode() { return Objects.hash(seatId); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof Seat)) return false;
        Seat other = (Seat) obj;
        return seatId.equals(other.seatId);
    }
}
