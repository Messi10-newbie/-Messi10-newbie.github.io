import java.util.Objects;

public abstract class ConcessionItem {
    private String name;
    private double price;
    private int    quantity;

    public ConcessionItem(String name, double price, int quantity) {
        this.name     = name;
        this.price    = price;
        this.quantity = quantity;
    }

    public String getName()            { return name; }
    public double getPrice()           { return price; }
    public int    getQuantity()        { return quantity; }
    public void   setQuantity(int qty) { this.quantity = qty; }

    public abstract String getCategory();

    public double getTotalCost() { return price * quantity; }

    @Override
    public String toString() {
        return getCategory() + "[" + name + " x" + quantity + " @ RM" + price + " = RM" + String.format("%.2f", getTotalCost()) + "]";
    }

    @Override
    public int hashCode() { return Objects.hash(name, getCategory()); }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof ConcessionItem)) return false;
        ConcessionItem other = (ConcessionItem) obj;
        return name.equals(other.name) && getCategory().equals(other.getCategory());
    }
}
